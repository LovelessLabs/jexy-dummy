PHPSTAN ?= /usr/local/bin/phpstan
COMPOSER ?= /usr/local/bin/composer
PHPUNIT ?= /usr/local/bin/phpunit
BOX ?= /usr/local/bin/box
ECS ?= vendor/bin/ecs

# get NEXT_VERSION from prep-release script
VERSION = $(shell cat .next-version)
REPOTOP = $(shell pwd)
UNAME = $(shell uname)

PKG = jexy-dummy

BLOCKSDIR ?= $(PKG)/blocks
CMDBUILDBLOCKS ?= npm run build

CMDBUILDADMIN ?= npm run build:admin

# we copy the whole directory in here first,
# then set the next version, then build the plugin
STAGEDIR ?= .stage

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
	@echo "	 pot                generate language file"
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

# generate language file
pot:
	@echo "generating language file"
	@cd $(PKG) && wp i18n make-pot . plugin/languages/$(PKG).pot && cd $(REPOTOP)
.PHONY: pot

# install dependencies
build-install-deps:
	-cd $(STAGEDIR)/$(PKG) && npm install && cd $(REPOTOP)
	-cd $(STAGEDIR)/$(PKG) && composer install && cd $(REPOTOP)
.PHONY: build-install-deps

# update dependencies
update-deps:
	cd $(PKG) && npm upgrade && cd $(REPOTOP)
	cd $(PKG) && composer update && cd $(REPOTOP)
.PHONY: update-deps

# clean up build files
clean:
	rm -rf build/* $(STAGEDIR)
.PHONY: clean

fresh-stage:
	rm -rf $(STAGEDIR)
	mkdir -p $(STAGEDIR)
	find $(PKG) -depth | grep -v node_modules | cpio -pdm $(STAGEDIR)
	-cp {LICENSE,CHANGELOG.md,README.md} $(STAGEDIR)
.PHONY: fresh-stage

build: clean fresh-stage readmetxt build-set-version build-install-deps build-blocks-js build-admin-ui
	# create build directory
	mkdir -p build/$(PKG)-$(VERSION)/$(PKG)
	# copy main files
	-cp $(STAGEDIR)/$(PKG)/composer.* build/$(PKG)-$(VERSION)/$(PKG)
	-cp $(STAGEDIR)/$(PKG)/*.php build/$(PKG)-$(VERSION)/$(PKG)
	-cp $(STAGEDIR)/LICENSE build/$(PKG)-$(VERSION)/$(PKG)
	-cp $(STAGEDIR)/readme.txt build/$(PKG)-$(VERSION)/$(PKG)
	# now copy directories
	-cp -R $(STAGEDIR)/$(PKG)/vendor build/$(PKG)-$(VERSION)/$(PKG)
	-cp -R $(STAGEDIR)/$(PKG)/plugin build/$(PKG)-$(VERSION)/$(PKG)
	# cleanup
	-cd build && find . -name '.DS_Store' -type f -delete && cd $(REPOTOP)
	cd build/$(PKG)-$(VERSION) && zip -r ../$(PKG)-$(VERSION).zip $(PKG) && cd $(REPOTOP)
	cd build/$(PKG)-$(VERSION) && tar -zcf ../$(PKG)-$(VERSION).tar.gz $(PKG) && cd $(REPOTOP)
.PHONY: build

build-blocks-js:
ifneq (,$(wildcard $(BLOCKSDIR)/src))
	mkdir -p build/$(PKG)-$(VERSION)/$(PKG)/blocks
	# build gutenberg blocks
	cd $(STAGEDIR)/$(PKG) && $(CMDBUILDBLOCKS) && cd $(REPOTOP)
	# copy block files
	cp -R $(STAGEDIR)/$(BLOCKSDIR)/dist build/$(PKG)-$(VERSION)/$(PKG)/blocks
else
	@echo "no blocks found in $(STAGEDIR)/$(BLOCKSDIR)/src, skipping build"
endif
.PHONY: build-blocks

build-admin-ui:
ifneq (,$(wildcard $(PKG)/plugin/admin/src))
	# build admin js & css
	cd $(STAGEDIR)/$(PKG) && $(CMDBUILDADMIN) && cd $(REPOTOP)
else
	@echo "no admin ui found in $(STAGEDIR)/$(PKG)/plugin/admin/src, skipping build"
endif
.PHONY: build-admin-ui

build-set-version:
ifeq ($(UNAME),Darwin)
	grep -rl RELEASE_VERSION --exclude-dir node_modules $(STAGEDIR) | xargs sed -i '' -e 's/RELEASE_VERSION/$(VERSION)/g'
else
	grep -rl RELEASE_VERSION --exclude-dir node_modules $(STAGEDIR) | xargs sed -i 's/RELEASE_VERSION/$(VERSION)/g'
endif
.PHONY: build-set-version

readmetxt:
	cat readme-partials/*.txt > $(STAGEDIR)/readme.txt
.PHONY: readmetxt

# package:
# 	mkdir -p build/$(PKG)-$(VERSION)/$(PKG)
# .PHONY: package

# Show to-do items per file
# requires ripgrep (rg) to be installed
todo:
	@rg --glob !Makefile '@todo|TODO' .
.PHONY: todo
