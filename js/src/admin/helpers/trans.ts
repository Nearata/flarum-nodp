import app from "flarum/admin/app";

export function adminTranslator(key: string, params = {}) {
	return app.translator.trans(`nearata-nodp.admin.${key}`, params);
}
