<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\campaign\controllers;

use Craft;
use craft\elements\User;
use craft\errors\MissingComponentException;
use craft\helpers\ArrayHelper;
use craft\helpers\MailerHelper;
use craft\mail\transportadapters\BaseTransportAdapter;
use craft\mail\transportadapters\Sendmail;
use craft\mail\transportadapters\TransportAdapterInterface;
use craft\web\Controller;
use craft\web\UrlManager;
use putyourlightson\campaign\Campaign;
use putyourlightson\campaign\elements\ContactElement;
use putyourlightson\campaign\helpers\SendoutHelper;
use putyourlightson\campaign\models\SettingsModel;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class SettingsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action): bool
    {
        if (!Craft::$app->getConfig()->getGeneral()->allowAdminChanges) {
            throw new ForbiddenHttpException(Craft::t('campaign', 'Administrative changes are disallowed in this environment.'));
        }

        // Require permission
        $this->requirePermission('campaign:settings');

        return parent::beforeAction($action);
    }

    /**
     * Edit general settings.
     *
     * @param SettingsModel|null $settings The settings being edited, if there were any validation errors.
     */
    public function actionEditGeneral(SettingsModel $settings = null): Response
    {
        if ($settings === null) {
            $settings = Campaign::$plugin->getSettings();
        }

        return $this->renderTemplate('campaign/settings/general', [
            'settings' => $settings,
            'config' => Craft::$app->getConfig()->getConfigFromFile('campaign'),
            'phpBinPath' => '/usr/bin/php',
            'isDynamicWebAliasUsed' => Campaign::$plugin->settings->isDynamicWebAliasUsed(),
        ]);
    }

    /**
     * Edit email settings.
     *
     * @param SettingsModel|null $settings The settings being edited, if there were any validation errors.
     * @param TransportAdapterInterface|null $adapter  The transport adapter, if there were any validation errors.
     */
    public function actionEditEmail(SettingsModel $settings = null, TransportAdapterInterface $adapter = null): Response
    {
        if ($settings === null) {
            $settings = Campaign::$plugin->getSettings();
        }

        if ($adapter === null) {
            $settings->transportType = $settings->transportType ?: Sendmail::class;
            try {
                $adapter = MailerHelper::createTransportAdapter($settings->transportType, $settings->transportSettings);
            }
            catch (MissingComponentException) {
                $adapter = new Sendmail();
                $adapter->addError('type', Craft::t('app', 'The transport type “{type}” could not be found.', [
                    'type' => $settings->transportType,
                ]));
            }
        }

        // Get all the registered transport adapter types
        $allTransportAdapterTypes = MailerHelper::allMailerTransportTypes();

        // Make sure the selected adapter class is in there
        if (!in_array(get_class($adapter), $allTransportAdapterTypes)) {
            $allTransportAdapterTypes[] = get_class($adapter);
        }

        $allTransportAdapters = [];
        $transportTypeOptions = [];

        foreach ($allTransportAdapterTypes as $transportAdapterType) {
            /** @var string|TransportAdapterInterface $transportAdapterType */
            if ($transportAdapterType === get_class($adapter) || $transportAdapterType::isSelectable()) {
                $allTransportAdapters[] = MailerHelper::createTransportAdapter($transportAdapterType);
                $transportTypeOptions[] = [
                    'value' => $transportAdapterType,
                    'label' => $transportAdapterType::displayName(),
                ];
            }
        }

        // Sort them by name
        ArrayHelper::multisort($transportTypeOptions, 'label');

        return $this->renderTemplate('campaign/settings/email', [
            'settings' => $settings,
            'config' => Craft::$app->getConfig()->getConfigFromFile('campaign'),
            'siteOptions' => Campaign::$plugin->settings->getSiteOptions(),
            'adapter' => $adapter,
            'allTransportAdapters' => $allTransportAdapters,
            'transportTypeOptions' => $transportTypeOptions,
        ]);
    }

    /**
     * Edit contact settings.
     */
    public function actionEditContact(): Response
    {
        $settings = Campaign::$plugin->getSettings();

        return $this->renderTemplate('campaign/settings/contact', [
            'fieldLayout' => $settings->getContactFieldLayout(),
            'config' => Craft::$app->getConfig()->getConfigFromFile('campaign'),
        ]);
    }

    /**
     * Edit sendout settings.
     *
     * @param SettingsModel|null $settings The settings being edited, if there were any validation errors.
     */
    public function actionEditSendout(SettingsModel $settings = null): Response
    {
        if ($settings === null) {
            $settings = Campaign::$plugin->getSettings();
        }

        return $this->renderTemplate('campaign/settings/sendout', [
            'settings' => $settings,
            'config' => Craft::$app->getConfig()->getConfigFromFile('campaign'),
            'system' => [
                'memoryLimit' => ini_get('memory_limit'),
                'memoryLimitExceeded' => (SendoutHelper::memoryInBytes($settings->memoryLimit) > SendoutHelper::memoryInBytes(ini_get('memory_limit'))),
                'timeLimit' => ini_get('max_execution_time'),
            ],
        ]);
    }

    /**
     * Edit GeoIP settings.
     *
     * @param SettingsModel|null $settings The settings being edited, if there were any validation errors.
     */
    public function actionEditGeoip(SettingsModel $settings = null): Response
    {
        if ($settings === null) {
            $settings = Campaign::$plugin->getSettings();
        }

        return $this->renderTemplate('campaign/settings/geoip', [
            'settings' => $settings,
            'config' => Craft::$app->getConfig()->getConfigFromFile('campaign'),
        ]);
    }

    /**
     * Edit Recaptcha settings.
     *
     * @param SettingsModel|null $settings The settings being edited, if there were any validation errors.
     */
    public function actionEditRecaptcha(SettingsModel $settings = null): Response
    {
        if ($settings === null) {
            $settings = Campaign::$plugin->getSettings();
        }

        return $this->renderTemplate('campaign/settings/recaptcha', [
            'settings' => $settings,
            'config' => Craft::$app->getConfig()->getConfigFromFile('campaign'),
        ]);
    }

    /**
     * Saves general settings.
     */
    public function actionSaveGeneral(): ?Response
    {
        $this->requirePostRequest();

        $settings = Campaign::$plugin->getSettings();

        // Set the simple stuff
        $settings->testMode = $this->request->getBodyParam('testMode', $settings->testMode);
        $settings->apiKey = $this->request->getBodyParam('apiKey', $settings->apiKey);
        $settings->mailgunWebhookSigningKey = $this->request->getBodyParam('mailgunWebhookSigningKey', $settings->mailgunWebhookSigningKey);

        // Save it
        if (!Campaign::$plugin->settings->saveSettings($settings)) {
            return $this->asModelFailure($settings, Craft::t('campaign', 'Couldn’t save general settings.'), 'settings');
        }

        return $this->asSuccess(Craft::t('campaign', 'General settings saved.'));
    }

    /**
     * Saves email settings.
     */
    public function actionSaveEmail(): ?Response
    {
        $this->requirePostRequest();

        $settings = $this->_getEmailSettingsFromPost();

        // Create the transport adapter so that we can validate it
        /** @var BaseTransportAdapter $adapter */
        $adapter = MailerHelper::createTransportAdapter($settings->transportType, $settings->transportSettings);

        // Validate transport adapter
        $adapter->validate();

        // Save it
        if ($adapter->hasErrors() || !Campaign::$plugin->settings->saveSettings($settings)) {
            return $this->asModelFailure($settings, Craft::t('campaign', 'Couldn’t save email settings.'), 'settings', [], [
                'adapter' => $adapter,
            ]);
        }

        return $this->asSuccess(Craft::t('campaign', 'Email settings saved.'));
    }

    /**
     * Saves contact settings.
     */
    public function actionSaveContact(): ?Response
    {
        $this->requirePostRequest();

        // Set the field layout
        $fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();
        $fieldLayout->type = ContactElement::class;

        // Save it
        if (!Campaign::$plugin->settings->saveContactFieldLayout($fieldLayout)) {
            return $this->asFailure(Craft::t('campaign', 'Couldn’t save contact settings.'));
        }

        return $this->asSuccess(Craft::t('campaign', 'Contact settings saved.'));
    }

    /**
     * Saves sendout settings.
     */
    public function actionSaveSendout(): ?Response
    {
        $this->requirePostRequest();

        $settings = Campaign::$plugin->getSettings();

        // Set the simple stuff
        $settings->maxBatchSize = $this->request->getBodyParam('maxBatchSize', $settings->maxBatchSize);
        $settings->memoryLimit = $this->request->getBodyParam('memoryLimit', $settings->memoryLimit);
        $settings->timeLimit = $this->request->getBodyParam('timeLimit', $settings->timeLimit);

        // Save it
        if (!Campaign::$plugin->settings->saveSettings($settings)) {
            return $this->asModelFailure($settings, Craft::t('campaign', 'Couldn’t save sendout settings.'), 'settings');
        }

        return $this->asSuccess(Craft::t('campaign', 'Sendout settings saved.'));
    }

    /**
     * Saves GeoIP settings.
     */
    public function actionSaveGeoip(): ?Response
    {
        $this->requirePostRequest();

        $settings = Campaign::$plugin->getSettings();

        // Set the simple stuff
        $settings->geoIp = $this->request->getBodyParam('geoIp', $settings->geoIp);
        $settings->ipstackApiKey = $this->request->getBodyParam('ipstackApiKey', $settings->ipstackApiKey);

        // Save it
        if (!Campaign::$plugin->settings->saveSettings($settings)) {
            return $this->asModelFailure($settings, Craft::t('campaign', 'Couldn’t save GeoIP settings.'), 'settings');
        }

        return $this->asSuccess(Craft::t('campaign', 'GeoIP settings saved.'));
    }

    /**
     * Saves Recaptcha settings.
     */
    public function actionSaveRecaptcha(): ?Response
    {
        $this->requirePostRequest();

        $settings = Campaign::$plugin->getSettings();

        // Set the simple stuff
        $settings->reCaptcha = $this->request->getBodyParam('reCaptcha', $settings->reCaptcha);
        $settings->reCaptchaSiteKey = $this->request->getBodyParam('reCaptchaSiteKey', $settings->reCaptchaSiteKey);
        $settings->reCaptchaSecretKey = $this->request->getBodyParam('reCaptchaSecretKey', $settings->reCaptchaSecretKey);
        $settings->reCaptchaErrorMessage = $this->request->getBodyParam('reCaptchaErrorMessage', $settings->reCaptchaErrorMessage);

        // Save it
        if (!Campaign::$plugin->settings->saveSettings($settings)) {
            return $this->asModelFailure($settings, Craft::t('campaign', 'Couldn’t save reCAPTCHA settings.'), 'settings');
        }

        return $this->asSuccess(Craft::t('campaign', 'reCAPTCHA settings saved.'));
    }

    /**
     * Sends a test email.
     */
    public function actionSendTestEmail(): ?Response
    {
        $this->requirePostRequest();

        $settings = $this->_getEmailSettingsFromPost();

        // Create the transport adapter so that we can validate it
        /** @var BaseTransportAdapter $adapter */
        $adapter = MailerHelper::createTransportAdapter($settings->transportType, $settings->transportSettings);

        // Validate settings and transport adapter
        $settings->validate();
        $adapter->validate();

        if ($settings->hasErrors() || $adapter->hasErrors()) {
            return $this->asModelFailure($settings, Craft::t('campaign', 'Couldn’t send test email.'), 'settings', [], [
                'adapter' => $adapter,
            ]);
        }

        // Create mailer with settings
        $mailer = Campaign::$plugin->createMailer($settings);

        // Get from name and email
        $fromNameEmail = Campaign::$plugin->settings->getFromNameEmail();

        $subject = Craft::t('campaign', 'This is a test email from Craft Campaign');
        $body = Craft::t('campaign', 'Congratulations! Craft Campaign was successfully able to send an email.');

        /** @var User $user */
        $user = Craft::$app->getUser()->getIdentity();

        $message = $mailer->compose()
            ->setFrom([$fromNameEmail['email'] => $fromNameEmail['name']])
            ->setTo($user->email)
            ->setSubject($subject)
            ->setHtmlBody($body)
            ->setTextBody($body);

        if ($fromNameEmail['replyTo']) {
            $message->setReplyTo($fromNameEmail['replyTo']);
        }

        if (!$message->send()) {
            return $this->asModelFailure($settings, Craft::t('campaign', 'Couldn’t send test email.'), 'settings', [], [
                'adapter' => $adapter,
            ]);
        }

        $this->setSuccessFlash(Craft::t('app', 'Email sent successfully! Check your inbox.'));

        // Send the settings and adapter back to the template
        /** @phpstan-var UrlManager $urlManager */
        $urlManager = Craft::$app->getUrlManager();
        $urlManager->setRouteParams([
            'settings' => $settings,
            'adapter' => $adapter,
        ]);

        return null;
    }

    /**
     * Returns email settings populated with post data.
     */
    private function _getEmailSettingsFromPost(): SettingsModel
    {
        $settings = Campaign::$plugin->getSettings();
        $settings->fromNamesEmails = $this->request->getBodyParam('fromNamesEmails', $settings->fromNamesEmails);
        $settings->transportType = $this->request->getBodyParam('transportType', $settings->transportType);
        $settings->transportSettings = $this->request->getBodyParam('transportTypes.' . $settings->transportType);

        return $settings;
    }
}
