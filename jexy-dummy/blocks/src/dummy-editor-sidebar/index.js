import { PanelRow } from '@wordpress/components';
// import { compose } from '@wordpress/compose';
// import { withDispatch, withSelect } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';

const JexyDummyDocumentSettingPanel = () => (
  <PluginDocumentSettingPanel
    name="jexy-dummy"
    className="jexy-dummy"
    title="Jexy Dummy"
  >
    <PanelRow>
      <p>{__("Panel content", "jexy-dummy")}</p>
    </PanelRow>
  </PluginDocumentSettingPanel>
);

registerPlugin('jexy-dummy-editor-sidebar', {
  render: JexyDummyDocumentSettingPanel,
});
