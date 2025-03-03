import { __ } from '@wordpress/i18n'
import { PluginDocumentSettingPanel } from '@wordpress/edit-post'
import { registerPlugin } from '@wordpress/plugins'
import { CheckboxControl } from '@wordpress/components'
import { useDispatch, useSelect } from '@wordpress/data'
import { store as coreStore } from '@wordpress/core-data'
import { store as editorStore } from '@wordpress/editor'

const VisibilityPanel = () => {

  // Get post's meta values and status
  const postId = useSelect(select => select(editorStore).getCurrentPostId(), [])
  const postType = useSelect(select => select(editorStore).getCurrentPostType(), [])
  const postStatus = useSelect(select => select(editorStore).getEditedPostAttribute('status'), [])

  // Only show for pending, future (scheduled) or private posts
  const isRelevantStatus = ['pending', 'future', 'private'].includes(postStatus)

  const { editEntityRecord } = useDispatch(coreStore)

  const overrideVisibility = useSelect(
    select => select(coreStore).getEditedEntityRecord('postType', postType, postId)?.meta?.kntnt_override_post_visibility,
    [postId, postType]
  )

  const addAlert = useSelect(
    select => select(coreStore).getEditedEntityRecord('postType', postType, postId)?.meta?.kntnt_override_post_visibility_alert,
    [postId, postType]
  )

  const addNoindex = useSelect(
    select => select(coreStore).getEditedEntityRecord('postType', postType, postId)?.meta?.kntnt_override_post_visibility_noindex,
    [postId, postType]
  )

  // Handle checkbox changes
  const updateMetaValue = (metaKey, value) => {
    editEntityRecord('postType', postType, postId, {
      meta: {
        [metaKey]: value
      }
    })
  }

  // Don't render the panel if post status is not relevant
  if (!isRelevantStatus) {
    return null
  }

  return (
    <PluginDocumentSettingPanel
      name="kntnt-override-post-visibility"
      title={__('Visibility Override', 'kntnt-override-post-visibility')}
    >
      <CheckboxControl
        label={__('Override visibility', 'kntnt-override-post-visibility')}
        checked={!!overrideVisibility}
        onChange={(value) => updateMetaValue('kntnt_override_post_visibility', value)}
      />

      {overrideVisibility && (
        <>
          <CheckboxControl
            label={__('Add notice', 'kntnt-override-post-visibility')}
            checked={!!addAlert}
            onChange={(value) => updateMetaValue('kntnt_override_post_visibility_alert', value)}
          />

          <CheckboxControl
            label={__('Add noindex meta tag', 'kntnt-override-post-visibility')}
            checked={!!addNoindex}
            onChange={(value) => updateMetaValue('kntnt_override_post_visibility_noindex', value)}
          />
        </>
      )}
    </PluginDocumentSettingPanel>
  )

}

registerPlugin('kntnt-override-post-visibility', {
  render: VisibilityPanel,
  icon: 'visibility'
})