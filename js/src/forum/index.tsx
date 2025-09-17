import Alert from "flarum/common/components/Alert";
import { extend } from "flarum/common/extend";
import app from "flarum/forum/app";
import ComposerBody from "flarum/forum/components/ComposerBody";
import EditPostComposer from "flarum/forum/components/EditPostComposer";
import type PostStreamState from "flarum/forum/states/PostStreamState";
import DiscussionControls from "flarum/forum/utils/DiscussionControls";
import { forumTranslator as trans } from "./helpers/trans";

export { default as extend } from "./extend";

app.initializers.add(
	"nearata-nodp",
	() => {
		// Add a warning message.
		extend(ComposerBody.prototype, "headerItems", function (items) {
			if (!this.attrs.nodp) {
				return;
			}

			const title = trans("composer_edit.double_posting_warning_title");
			const description = trans(
				"composer_edit.double_posting_warning_description",
			);

			items.add(
				"nodp",
				<Alert dismissible={false} title={title} type="warning">
					{description}
				</Alert>,
				-10,
			);
		});

		extend(DiscussionControls, "replyAction", () => {
			const user = app.session.user;

			if (!user) {
				return;
			}

			const stream: PostStreamState = app.current.get("stream");

			if (stream.discussion.canDoublePost()) {
				return;
			}

			const posts = stream.posts().filter((post) => {
				return (
					post.contentType() === "comment" && post.user().id() === user.id()
				);
			});

			if (!posts.length) {
				return;
			}

			// last post
			const post = posts[posts.length - 1];

			if (post?.canEdit()) {
				// close the old composer if still open
				if (app.composer.editor != null) {
					app.composer.close();
				}

				app.composer.load(EditPostComposer, { post, nodp: true });
				app.composer.show();
			} else {
				// user can't edit their post
				// and not allowed to double post.
				app.alerts.show(
					Alert,
					{ type: "error" },
					trans("discussion.cannot_reply_alert_message"),
				);
				app.composer.close();
			}
		});
	},
	-10,
);
