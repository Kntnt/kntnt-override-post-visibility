(function ($) {
  // Check if we have any posts in our visibility data
  const hasOverridablePosts = function () {
    return kntnt_override_post_visibility.visibility &&
      Object.keys(kntnt_override_post_visibility.visibility).length > 0
  }

  // Add the dropdown to Quick Edit
  $(document).on('click', '.editinline', function () {
    // Get the post ID
    var postId = $(this).closest('tr').attr('id').substr(5)

    // Check if this post is overridable (pending, future, private)
    var isOverridable = kntnt_override_post_visibility.visibility &&
      kntnt_override_post_visibility.visibility[postId] !== undefined

    if (!isOverridable) {
      return // Skip adding the control for non-overridable posts
    }

    // Add the dropdown to Quick Edit form
    var $quickEditRow = $('#edit-' + postId)
    var $visibilityField = $('<div class="inline-edit-group"><label><span class="title">' +
      kntnt_override_post_visibility.label +
      '</span><select name="kntnt_override_post_visibility"></select></label></div>')

    // Add options to the dropdown
    var $select = $visibilityField.find('select')
    $.each(kntnt_override_post_visibility.options, function (value, label) {
      $select.append($('<option></option>').attr('value', value).text(label))
    })

    // Add the field after the status dropdown
    $quickEditRow.find('.inline-edit-status').after($visibilityField)

    // Try to set the current value
    try {
      var data = kntnt_override_post_visibility.visibility[postId]

      // Determine which option to select
      var selectValue = 'off'
      if (data.override) {
        if (data.alert && data.noindex) {
          selectValue = 'on_with_alert_with_noindex'
        } else if (data.alert) {
          selectValue = 'on_with_alert'
        } else if (data.noindex) {
          selectValue = 'on_with_noindex'
        } else {
          selectValue = 'on'
        }
      }

      $select.val(selectValue)
    } catch (e) {
      console.error('Error setting current visibility value:', e)
    }
  })

  // Add the dropdown to Bulk Edit
  $(document).ready(function () {
    // Only add the field if we have overridable posts
    if (!hasOverridablePosts()) {
      return
    }

    var $bulkEditRow = $('#bulk-edit')
    var $visibilityField = $('<div class="inline-edit-group"><label><span class="title">' +
      kntnt_override_post_visibility.label +
      '</span><select name="kntnt_override_post_visibility"></select></label></div>')

    // Add options to the dropdown
    var $select = $visibilityField.find('select')
    $.each(kntnt_override_post_visibility.options, function (value, label) {
      $select.append($('<option></option>').attr('value', value).text(label))
    })

    // Add the field after the status dropdown
    $bulkEditRow.find('.inline-edit-status').after($visibilityField)
  })
})(jQuery)