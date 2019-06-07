export default interface DataModal {
	show(postId: number, mediaId?: string): void,

	close(): void,

	reset(): void

	isOpen(): boolean
}