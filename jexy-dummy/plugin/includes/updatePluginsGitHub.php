<?php

/**
 * testing needs:
 * - get_file_data() returns an array of meta data
 * - plugin_basename() returns a string
 * - add_filter() returns a callable
 * - get_bloginfo() returns an array of meta data
 * - wp_remote_get() returns mixed WP_HTTP_Response|WP_Error
 * - is_wp_error() returns a boolean
 * - wp_remote_retrieve_body() returns a string (in this case, JSON)
 * - apply_filters() returns a callable
 */
return function (string $pluginFile) {
    return new class($pluginFile)
    {
        protected $meta;
        protected $pluginFile;
        protected $pluginSlug;
        protected $releaseChannels = array(
            'stable'
        );
        protected $stableAliases = array(
            'master',
            'main'
        );

        public function __construct(string $pluginFile)
        {
            $this->meta = get_file_data($pluginFile, [
                'Version' => 'Version',
                'RepoVisibility' => 'Repo Visibility',
                'ReleaseChannels' => 'Release Channels',
            ], 'plugin');

            $this->pluginFile = $pluginFile;
            $this->pluginSlug = plugin_basename($pluginFile);

            if (!empty($this->meta['ReleaseChannels'])) {
                $this->releaseChannels = array_map('trim', explode(',', $this->meta['ReleaseChannels']));
            }
            // debugging
            do_action('qm/debug', $pluginFile);
            add_filter('update_plugins_github.com', [$this, 'onUpdateGitHubPlugins'], 10, 4);
        }

        /**
         * See the UpdateURI section of wp_update_plugins() in wp-admin/includes/update.php
         *
         * @see https://developer.wordpress.org/reference/functions/wp_update_plugins/
         * @see https://developer.wordpress.org/reference/hooks/update_plugins_hostname/
         *
         * @param mixed $update
         * @param string $pluginFile
         * @param array $pluginData
         * @param mixed $locales
         * @return mixed
         */
        public function onUpdateGitHubPlugins($update, $pluginFile, $pluginData, $locales)
        {
            // debugging
            do_action('qm/debug', print_r($pluginData, true));

            // if this is not our plugin, bail
            if ($this->pluginFile !== $pluginFile) {
                return $update;
            }

            $releases = $this->getLatestViableReleases();
            if ($releases == false) {
                return $update;
            }

            // $update->response[$this->pluginSlug] = (object) [
            //     'slug' => $this->pluginSlug,
            //     'plugin' => $this->pluginSlug,
            //     'new_version' => $releases['new_version'],
            //     'package' => $releases['package'],
            //     'url' => $releases['url'],
            //     'icons' => [
            //         '1x' => 'https://raw.githubusercontent.com/jexy-org/jexy-dummy/main/assets/icon-128x128.png',
            //         '2x' => 'https://raw.githubusercontent.com/jexy-org/jexy-dummy/main/assets/icon-256x256.png',
            //     ],
            //     'banners' => [
            //         'low' => 'https://raw.githubusercontent.com/jexy-org/jexy-dummy/main/assets/banner-772x250.png',
            //         'high' => 'https://raw.githubusercontent.com/jexy-org/jexy-dummy/main/assets/banner-1544x500.png',
            //     ],
            //     'banners_rtl' => [
            //         'low' => 'https://raw.githubusercontent.com/jexy-org/jexy-dummy/main/assets/banner-772x250.png',
            //         'high' => 'https://raw.githubusercontent.com/jexy-org/jexy-dummy/main/assets/banner-1544x500.png',
            //     ],
            // ];

            // TODO determine selection for viable update channels.
            // for now, we return the first one that's not null
            foreach ($releases as $channel => $info) {
                if ($info !== null) {
                    $update = $info;
                    break;
                }
            }

            return $update;
        }

        /**
         * If you need to add a GitHub access token to your requests, you can do so here, using the
         * update_plugins_github.com_headers filter or the update_plugins_github.com_{pluginSlug}_headers
         * filter (which are called in that order).
         *
         * @return array
         */
        private function prepRequestHeaders()
        {
            // is this a private repo?
            $visibility = $this->meta['RepoVisibility'] ?? 'public';

            $args = [
                'httpversion' => '1.1',
                'headers' => [
                    'Accept' => 'application/vnd.github.v3+json,application/json',
                    'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . get_bloginfo('url'),
                    'X-GitHub-Api-Version' => '2022-11-28'
                ],
            ];

            $args = apply_filters('update_plugins_github.com_headers', $args, $visibility);
            $args = apply_filters('update_plugins_github.com_' . $this->pluginSlug . '_headers', $args, $visibility);

            return $args;
        }

        /**
         * Fetch releases data from the GitHub API.
         *
         * We will consider any release matching the current release channels and containing an
         * update-info.json file as one of the release artifacts.
         *
         * The update-info.json file should contain metadata about the release, including the version number.
         *
         * @since 1.0.0
         */
        private function getRemoteReleases()
        {
            $args = $this->prepRequestHeaders();

            /**
             * The `update_plugins_github.com_{pluginSlug}_host` filter.
             *
             * If you need to change the hostname to something other than api.github.com,
             * you can do so here. This might be useful if you are using GitHub Enterprise.
             *
             * @since 1.0.0
             */
            $apiHost = apply_filters('update_plugins_github.com_' . $this->pluginSlug . '_apihost', 'api.github.com');
            $url = 'https://' . $apiHost . '/repos/' . $this->meta['GitHubRepo'] . '/releases';

            /**
             * The `update_plugins_github.com_{pluginSlug}_release_channels` filter.
             *
             * This filter allows setting the release channel to something other
             * than (or in addition to) 'stable'.
             *
             * If the only release channel is 'stable' (the default), then we will use the GitHub API's
             * 'latest release' endpoint, which is more efficient than fetching all releases.
             *
             * If this filter results in multiple release channels, we will fetch all releases and
             * filter them by the value of release 'target_commitish' (which is the branch name
             * the release was merged into).
             *
             * @since 1.0.0
             *
             * @see https://semantic-release.gitbook.io/semantic-release/usage/configuration#branches
             * @see https://docs.github.com/en/rest/releases/releases?apiVersion=2022-11-28#get-the-latest-release
             */
            $this->releaseChannels = apply_filters(
                'update_plugins_github.com_' . $this->pluginSlug . '_release_channels',
                $this->releaseChannels
            );

            if (count($this->releaseChannels) === 1 && $this->releaseChannels[0] === 'stable') {
                $url .= '/latest';
            }

            $response = wp_remote_get($url, $args);

            if (is_wp_error($response)) {
                return false;
            }
            do_action('qm/debug', wp_remote_retrieve_body($response));
            $data = json_decode(wp_remote_retrieve_body($response), true);

            if (empty($data)) {
                return false;
            }

            return $data;
        }

        /**
         * A "viable release" for our purposes is defined as a release with at least two release assets:
         *
         * - a zip file containing the plugin
         * - a json file containing metadata about the release
         *
         * The default name for the json file is "info.json", but this can be changed using the
         * update_plugins_github.com_{pluginSlug}_info_json filter.
         *
         * @return array|false
         */
        private function getLatestViableReleases()
        {
            $releases = $this->getRemoteReleases();

            if (empty($releases)) {
                return false;
            }

            $latest = [];
            foreach ($this->releaseChannels as $channel) {
                $latest[$channel] = null;
            }

            $infoJsonFile = apply_filters(
                'update_plugins_github.com_' . $this->pluginSlug . '_info_json',
                'update-info.json'
            );

            $stableAliases = apply_filters(
                'update_plugins_github.com_' . $this->pluginSlug . '_stable_aliases',
                $this->stableAliases
            );

            foreach ($releases as $release) {
                // skip releases with no assets
                if (empty($release['assets'])) {
                    continue;
                }

                // skip draft releases
                if ($release['draft']) {
                    continue;
                }

                // skip releases that don't have an info json asset
                $info = array_filter($release['assets'], function ($asset) use ($infoJsonFile) {
                    return $asset['name'] === $infoJsonFile;
                });
                if (empty($info)) {
                    continue;
                }

                // skip releases that don't have a zip asset
                $zip = array_filter($release['assets'], function ($asset) {
                    return $asset['content_type'] === 'application/zip';
                });
                if (empty($zip)) {
                    continue;
                }

                // skip release channels we don't want (or don't recognize)
                $channel = $release['target_commitish'];
                if (!in_array($channel, $this->releaseChannels)) {
                    // maybe it's an alias?
                    if (in_array($channel, $stableAliases)) {
                        $channel = 'stable';
                    } else {
                        continue;
                    }
                }

                // if we already found this latest for this channel, skip it
                if (!empty($latest[$channel])) {
                    continue;
                }

                // hey, looks good. let's fetch the info.json file and check it.
                $args = $this->prepRequestHeaders();
                do_action('qm/debug', $info['browser_download_url']);
                $response = wp_remote_get(
                    $info['browser_download_url'],
                    $args
                );

                if (is_wp_error($response)) {
                    // couldn't get info for this release, so skip it
                    continue;
                }

                // the response _should_ be ready to decode and hand back without
                // any further processing other than assigning the browser_download_url
                do_action('qm/debug', wp_remote_retrieve_body($response));
                $data = json_decode(wp_remote_retrieve_body($response));
                if (!empty($data)) {
                    // $data['download_url'] = $zip['browser_download_url'];
                    if ($data->package === 'browser_download_url') {
                        $data->package = $zip['browser_download_url'];
                    }
                    $latest[$channel] = $data;
                }
            }

            return $latest;
        }
    };
};
