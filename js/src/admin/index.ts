import app from "flarum/admin/app";

app.initializers.add("nearata-nodp", () => {
	app.extensionData
		.for("nearata-nodp")
		.registerSetting({
			setting: "nearata-nodp.time_limit",
			type: "number",
			label: app.translator.trans(
				"nearata-nodp.admin.settings.time_limit_label",
			),
			help: app.translator.trans("nearata-nodp.admin.settings.time_limit_text"),
		})
		.registerPermission(
			{
				icon: "far fa-clone",
				label: app.translator.trans(
					"nearata-nodp.admin.permissions.double_posting_label",
				),
				permission: "discussion.doublePost",
			},
			"reply",
		);
});
