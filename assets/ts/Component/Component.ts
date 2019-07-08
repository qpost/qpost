import DeleteModal from "./DataModal/DeleteModal";
import MediaModal from "./DataModal/MediaModal";
import StatusModal from "./DataModal/StatusModal";
import AdSense from "./AdSense";
import ToggleNSFW from "./PostForm/ToggleNSFW";
import AddPhoto from "./PostForm/AddPhoto";
import ImageUpload from "./ImageUpload";
import LayeredModals from "./LayeredModals";
import TextButton from "./PostForm/TextButton";
import NSFWInfo from "./Post/NSFWInfo";
import StatusTrigger from "./Post/StatusTrigger";
import DeleteButton from "./Post/DeleteButton";
import ReplyButton from "./Post/ReplyButton";
import LateTooltip from "./LateTooltip";
import FavoriteButton from "./Post/FavoriteButton";
import ShareButton from "./Post/ShareButton";
import Utility from "./Utility";
import CharacterCount from "./PostForm/CharacterCount";
import Highlight from "./PostForm/Highlight";
import LinkButton from "./PostForm/LinkButton";
import VideoButton from "./PostForm/VideoButton";
import FilterLink from "./FilterLink";
import PostButton from "./PostForm/PostButton";
import Notifications from "./Notifications";
import ShareCount from "./Post/ShareCount";
import FavoriteCount from "./Post/FavoriteCount";
import FollowButton from "./FollowButton";
import HomeFeed from "./HomeFeed";
import Base from "./Base";
import PostField from "./PostForm/PostField";

export default class Component {
	public static deleteModal: DeleteModal;
	public static mediaModal: MediaModal;
	public static statusModal: StatusModal;

	public static init(): void {
		DeleteButton.init();
		FavoriteButton.init();
		FavoriteCount.init();
		NSFWInfo.init();
		ReplyButton.init();
		ShareButton.init();
		ShareCount.init();
		StatusTrigger.init();

		AddPhoto.init();
		CharacterCount.init();
		Highlight.init();
		LinkButton.init();
		PostButton.init();
		PostField.init();
		TextButton.init();
		ToggleNSFW.init();
		VideoButton.init();

		AdSense.init();
		Base.init();
		FilterLink.init();
		FollowButton.init();
		HomeFeed.init();
		ImageUpload.init();
		LateTooltip.init();
		LayeredModals.init();
		Notifications.init();
		Utility.init();

		this.deleteModal = new DeleteModal();
		this.mediaModal = new MediaModal();
		this.statusModal = new StatusModal();
	}
}