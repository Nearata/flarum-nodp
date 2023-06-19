import app from 'flarum/admin/app';

app.initializers.add('the-turk-nodp', () => {
  app.extensionData
    .for('the-turk-nodp')
    .registerSetting({
      setting: 'the-turk-nodp.time_limit',
      type: 'number',
      label: app.translator.trans('the-turk-nodp.admin.settings.time_limit_label'),
      help: app.translator.trans('the-turk-nodp.admin.settings.time_limit_text'),
    })
    .registerSetting({
      setting: 'the-turk-nodp.allow_dp_no_replies_minutes',
      type: 'number',
      label: app.translator.trans('the-turk-nodp.admin.settings.allow_dp_no_replies_minutes_label')
    })
    .registerPermission(
      {
        icon: 'far fa-clone',
        label: app.translator.trans('the-turk-nodp.admin.permissions.double_posting_label'),
        permission: 'discussion.doublePost',
      },
      'reply'
    );
});
