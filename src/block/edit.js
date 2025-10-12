import { __ } from '@wordpress/i18n';
import {
	InspectorControls,
	useBlockProps
} from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	ToggleControl,
	RangeControl,
	Button,
	Spinner,
	Notice,
	Placeholder
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import ServerSideRender from '@wordpress/server-side-render';
import './editor.css';

export default function Edit({ attributes, setAttributes }) {
	const {
		instance,
		account,
		tag,
		limit,
		excludeBoosts,
		excludeReplies,
		onlyPinned,
		onlyMedia,
		showPreviewCards,
		showPostAuthor,
		showDateTime
	} = attributes;

	// Get defaults from localized settings (passed from PHP)
	const defaults = window.mastodonFeedDefaults || {};

	// Helper to get attribute value with fallback to admin default
	const getAttributeValue = (attrValue, defaultValue) => {
		return attrValue !== undefined ? attrValue : defaultValue;
	};

	const [lookupHandle, setLookupHandle] = useState('');
	const [isLookingUp, setIsLookingUp] = useState(false);
	const [lookupError, setLookupError] = useState('');
	const [lookupSuccess, setLookupSuccess] = useState('');

	// Local state for placeholder inputs (prevents premature closing)
	const [placeholderAccount, setPlaceholderAccount] = useState('');
	const [placeholderTag, setPlaceholderTag] = useState('');
	const [placeholderInstance, setPlaceholderInstance] = useState(instance || 'mastodon.social');

	const blockProps = useBlockProps({
		className: 'mastodon-feed-block-editor'
	});

	// Function to lookup account ID from handle using WordPress REST API
	const lookupAccountId = async () => {
		if (!lookupHandle) {
			setLookupError(__('Please enter a Mastodon handle (e.g., username@mastodon.social)', 'mastodon-feed'));
			return;
		}

		setIsLookingUp(true);
		setLookupError('');
		setLookupSuccess('');

		try {
			// Use WordPress REST API endpoint (server-side lookup to avoid CORS and authentication issues)
			const response = await apiFetch({
				path: '/mastodon-feed/v1/lookup-account',
				method: 'POST',
				data: {
					handle: lookupHandle.trim()
				}
			});

			if (response.success && response.account) {
				setAttributes({
					account: response.account.id,
					instance: response.instance
				});
				setLookupSuccess(__(`Found account: @${response.account.acct} (ID: ${response.account.id})`, 'mastodon-feed'));
				setLookupHandle('');
			} else {
				throw new Error(__('No account found with that handle.', 'mastodon-feed'));
			}
		} catch (error) {
			// Extract error message from WordPress REST API error response
			const errorMessage = error.message || __('Failed to lookup account. Please verify the handle and try again.', 'mastodon-feed');
			setLookupError(errorMessage);
		} finally {
			setIsLookingUp(false);
		}
	};

	// Finalize the placeholder setup and switch to main view
	const finalizePlaceholderSetup = () => {
		if (placeholderAccount || placeholderTag) {
			setAttributes({
				account: placeholderAccount || '',
				tag: placeholderTag || '',
				instance: placeholderInstance
			});
		}
	};

	// Show placeholder if no account or tag is set
	if (!account && !tag) {
		return (
			<div {...blockProps}>
				<Placeholder
					icon="rss"
					label={__('Mastodon Feed', 'mastodon-feed')}
					instructions={__('Enter your Mastodon account ID or use the lookup tool to find it.', 'mastodon-feed')}
				>
					<div className="mastodon-feed-placeholder-content">
						<TextControl
							label={__('Mastodon Handle', 'mastodon-feed')}
							value={lookupHandle}
							onChange={setLookupHandle}
							placeholder="username@instance.domain"
							help={__('Enter your full Mastodon handle (e.g., username@mastodon.social)', 'mastodon-feed')}
						/>
						<Button
							variant="primary"
							onClick={lookupAccountId}
							disabled={isLookingUp}
						>
							{isLookingUp ? <Spinner /> : __('Lookup Account ID', 'mastodon-feed')}
						</Button>

						{lookupError && (
							<Notice status="error" isDismissible={false}>
								{lookupError}
							</Notice>
						)}

						{lookupSuccess && (
							<Notice status="success" isDismissible={false}>
								{lookupSuccess}
							</Notice>
						)}

						<div className="mastodon-feed-placeholder-divider">
							{__('or', 'mastodon-feed')}
						</div>

						<TextControl
							label={__('Instance', 'mastodon-feed')}
							value={placeholderInstance}
							onChange={setPlaceholderInstance}
							placeholder="mastodon.social"
							help={__('Mastodon instance domain (without https://)', 'mastodon-feed')}
						/>

						<TextControl
							label={__('Account ID', 'mastodon-feed')}
							value={placeholderAccount}
							onChange={setPlaceholderAccount}
							onBlur={finalizePlaceholderSetup}
							placeholder="109321514573003627"
							help={__('Your Mastodon account ID (a long number)', 'mastodon-feed')}
						/>

						<div className="mastodon-feed-placeholder-divider">
							{__('or', 'mastodon-feed')}
						</div>

						<TextControl
							label={__('Tag', 'mastodon-feed')}
							value={placeholderTag}
							onChange={setPlaceholderTag}
							onBlur={finalizePlaceholderSetup}
							placeholder="photography"
							help={__('Show tag feed instead of account feed (without # symbol)', 'mastodon-feed')}
						/>
					</div>
				</Placeholder>
			</div>
		);
	}

	// Show the feed preview
	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Account Lookup', 'mastodon-feed')} initialOpen={true}>
					<TextControl
						label={__('Mastodon Handle', 'mastodon-feed')}
						value={lookupHandle}
						onChange={setLookupHandle}
						placeholder="username@instance.domain"
						help={__('Lookup account ID from handle', 'mastodon-feed')}
					/>
					<Button
						variant="secondary"
						onClick={lookupAccountId}
						disabled={isLookingUp}
						style={{ marginBottom: '12px' }}
					>
						{isLookingUp ? <Spinner /> : __('Lookup Account ID', 'mastodon-feed')}
					</Button>

					{lookupError && (
						<Notice status="error" isDismissible={true} onRemove={() => setLookupError('')}>
							{lookupError}
						</Notice>
					)}

					{lookupSuccess && (
						<Notice status="success" isDismissible={true} onRemove={() => setLookupSuccess('')}>
							{lookupSuccess}
						</Notice>
					)}
				</PanelBody>

				<PanelBody title={__('Feed Settings', 'mastodon-feed')} initialOpen={true}>
					<TextControl
						label={__('Instance', 'mastodon-feed')}
						value={instance}
						onChange={(value) => setAttributes({ instance: value })}
						help={__('Mastodon instance domain', 'mastodon-feed')}
					/>

					<TextControl
						label={__('Account ID', 'mastodon-feed')}
						value={account}
						onChange={(value) => setAttributes({ account: value })}
						help={__('Your Mastodon account ID', 'mastodon-feed')}
					/>

					<TextControl
						label={__('Tag', 'mastodon-feed')}
						value={tag}
						onChange={(value) => setAttributes({ tag: value })}
						help={__('Show tag feed instead (without #)', 'mastodon-feed')}
					/>

					<RangeControl
						label={__('Number of Posts', 'mastodon-feed')}
						value={limit}
						onChange={(value) => setAttributes({ limit: value })}
						min={1}
						max={40}
					/>
				</PanelBody>

				<PanelBody title={__('Filter Options', 'mastodon-feed')} initialOpen={false}>
                    <ToggleControl
                        label={__('Only Pinned Posts', 'mastodon-feed')}
                        checked={getAttributeValue(onlyPinned, defaults.onlyPinned)}
                        onChange={(value) => setAttributes({ onlyPinned: value })}
                    />

                    <ToggleControl
                        label={__('Only Media Posts', 'mastodon-feed')}
                        checked={getAttributeValue(onlyMedia, defaults.onlyMedia)}
                        onChange={(value) => setAttributes({ onlyMedia: value })}
                    />

					<ToggleControl
						label={__('Exclude Boosts', 'mastodon-feed')}
						checked={getAttributeValue(excludeBoosts, defaults.excludeBoosts)}
						onChange={(value) => setAttributes({ excludeBoosts: value })}
					/>

					<ToggleControl
						label={__('Exclude Replies', 'mastodon-feed')}
						checked={getAttributeValue(excludeReplies, defaults.excludeReplies)}
						onChange={(value) => setAttributes({ excludeReplies: value })}
					/>
				</PanelBody>

				<PanelBody title={__('Display Options', 'mastodon-feed')} initialOpen={false}>
					<ToggleControl
						label={__('Show Preview Cards', 'mastodon-feed')}
						checked={getAttributeValue(showPreviewCards, defaults.showPreviewCards)}
						onChange={(value) => setAttributes({ showPreviewCards: value })}
					/>

					<ToggleControl
						label={__('Show Post Author', 'mastodon-feed')}
						checked={getAttributeValue(showPostAuthor, defaults.showPostAuthor)}
						onChange={(value) => setAttributes({ showPostAuthor: value })}
					/>

					<ToggleControl
						label={__('Show Date & Time', 'mastodon-feed')}
						checked={getAttributeValue(showDateTime, defaults.showDateTime)}
						onChange={(value) => setAttributes({ showDateTime: value })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<ServerSideRender
					block="mastodon-feed/embed"
					attributes={attributes}
				/>
			</div>
		</>
	);
}
