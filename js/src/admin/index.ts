import app from "flarum/admin/app";
import { adminTranslator as trans } from "./helpers/trans";

app.initializers.add("nearata-nodp", () => {
	app.extensionData
		.for("nearata-nodp")
		.registerSetting({
			setting: "nearata-nodp.time_limit",
			type: "number",
			label: trans("settings.time_limit_label"),
			help: trans("settings.time_limit_text"),
		})
		.registerPermission(
			{
				icon: "far fa-clone",
				label: trans("permissions.double_posting_label"),
				permission: "discussion.doublePost",
			},
			"reply",
		);
});
