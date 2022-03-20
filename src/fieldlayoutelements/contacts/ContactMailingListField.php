<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\campaign\fieldlayoutelements\contacts;

use Craft;
use craft\base\ElementInterface;
use craft\fieldlayoutelements\BaseNativeField;
use putyourlightson\campaign\assets\ContactEditAsset;
use putyourlightson\campaign\assets\ReportsAsset;
use putyourlightson\campaign\Campaign;
use putyourlightson\campaign\elements\MailingListElement;

/**
 * @since 2.0.0
 */
class ContactMailingListField extends BaseNativeField
{
    /**
     * @inheritdoc
     */
    public string $attribute = 'contact-mailinglist';

    /**
     * @inheritdoc
     */
    protected function inputHtml(?ElementInterface $element = null, bool $static = false): ?string
    {
        return null;
    }

    /**
     * @inheritdoc
     * @param MailingListElement $element
     */
    public function formHtml(?ElementInterface $element = null, bool $static = false): ?string
    {
        Craft::$app->getView()->registerAssetBundle(ContactEditAsset::class);

        return Craft::$app->view->renderTemplate(
            'campaign/contacts/_includes/mailinglists',
            [
                'contact' => $element,
                'mailingLists' => Campaign::$plugin->mailingLists->getAllMailingLists(),
            ],
        );
    }
}