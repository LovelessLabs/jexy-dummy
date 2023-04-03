PHPSTAN ?= /usr/local/bin/phpstan
COMPOSER ?= /usr/local/bin/composer
PHPUNIT ?= /usr/local/bin/phpunit
BOX ?= /usr/local/bin/box
ECS ?= vendor/bin/ecs

# get NEXT_VERSION from prep-release script
VERSION = $(shell cat .next-version)
REPOTOP = $(shell pwd)

PKG = jexy-dummy

BLOCKSDIR ?= $(PKG)/blocks
CMDBUILDBLOCKS ?= npm run build

CMDBUILDADMIN ?= npm run build:admin

all: target-list

# reminders of what make commands are set up
target-list:
	@echo useful targets:
	@echo
	@echo "  build              build the plugin"
	@echo "  clean              clean up build files"
	@echo "  install-deps       install npm and composer dependencies"
	@echo "  update-deps        update npm and composer dependencies"
	@echo "  cs                 run coding style checks"
	@echo "  cloc               generate Lines-of-Code stats"
	@echo "  todo               show to-do items per file"
	@echo "  test               run all tests"
	@echo "  test-coverage      run all tests, with code coverage"
	@echo
.PHONY: target-list

# for debuging makefile vars
check-vars:
	@echo Version
	@echo $(VERSION)
.PHONY: check-vars

# install dependencies
install-deps:
ifneq (,$(wildcard $(PKG)/package-lock.json))
	cd $(PKG) && npm install && cd $(REPOTOP)
else
	@echo "no package-lock.json found, skipping npm install"
endif
ifneq (,$(wildcard $(PKG)/composer.lock))
	cd $(PKG) && composer install && cd $(REPOTOP)
else
	@echo "no composer.lock found, skipping composer install"
endif
.PHONY: install-deps

# update dependencies
update-deps:
	cd $(PKG) && npm upgrade && cd $(REPOTOP)
	cd $(PKG) && composer update && cd $(REPOTOP)
.PHONY: update-deps

# clean up build files
clean:
	rm -rf build/*
.PHONY: clean

build: clean install-deps build-blocks-js build-admin-ui
	# create build directory
	mkdir -p build/$(PKG)-$(VERSION)/$(PKG)
	# copy main files
	-cp $(PKG)/composer.* build/$(PKG)-$(VERSION)/$(PKG)
	-cp $(PKG)/*.php build/$(PKG)-$(VERSION)/$(PKG)
	-cp $(PKG)/readme.txt build/$(PKG)-$(VERSION)/$(PKG)
	-cp LICENSE build/$(PKG)-$(VERSION)/$(PKG)
	-cp CHANGELOG.md build/$(PKG)-$(VERSION)/$(PKG)
	# now copy directories
	-cp -R $(PKG)/vendor build/$(PKG)-$(VERSION)/$(PKG)
	-cp -R $(PKG)/plugin build/$(PKG)-$(VERSION)/$(PKG)
	# dump autoloader
	-cd build/$(PKG)-$(VERSION)/$(PKG) && composer dump-autoload --optimize && cd $(REPOTOP)
	-cd build && find . -name '.DS_Store' -type f -delete && cd $(REPOTOP)
	cd build/$(PKG)-$(VERSION) && zip -r ../$(PKG)-$(VERSION).zip $(PKG) && cd $(REPOTOP)
	cd build/$(PKG)-$(VERSION) && tar -zcf ../$(PKG)-$(VERSION).tar.gz $(PKG) && cd $(REPOTOP)
.PHONY: build

build-blocks-js:
ifneq (,$(wildcard $(BLOCKSDIR)/src))
	mkdir -p build/$(PKG)-$(VERSION)/$(PKG)/blocks
	# build gutenberg blocks
	cd $(PKG) && $(CMDBUILDBLOCKS) && cd $(REPOTOP)
	# copy block files
	cp -R $(BLOCKSDIR)/dist build/$(PKG)-$(VERSION)/$(PKG)/blocks
else
	@echo "no blocks found in $(BLOCKSDIR)/src, skipping build"
endif
.PHONY: build-blocks

build-admin-ui:
ifneq (,$(wildcard $(PKG)/plugin/admin/src))
	# build admin js & css
	cd $(PKG) && $(CMDBUILDADMIN) && cd $(REPOTOP)
else
	@echo "no admin ui found in $(PKG)/plugin/admin/src, skipping build"
endif
.PHONY: build-admin-ui


# package:
# 	mkdir -p build/$(PKG)-$(VERSION)/$(PKG)
# .PHONY: package

# Show to-do items per file
# requires ripgrep (rg) to be installed
todo:
	@rg --glob !Makefile '@todo|TODO' .
.PHONY: todo
