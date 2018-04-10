<?php
/**
 * @link      https://craftcampaign.com
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\campaign\services;

use putyourlightson\campaign\Campaign;
use putyourlightson\campaign\elements\CampaignElement;
use putyourlightson\campaign\elements\ContactElement;
use putyourlightson\campaign\elements\MailingListElement;
use putyourlightson\campaign\elements\SendoutElement;
use putyourlightson\campaign\models\ContactActivityModel;
use putyourlightson\campaign\models\LinkModel;
use putyourlightson\campaign\models\ContactCampaignModel;
use putyourlightson\campaign\models\ContactMailingListModel;
use putyourlightson\campaign\records\ContactRecord;
use putyourlightson\campaign\records\LinkRecord;
use putyourlightson\campaign\records\ContactCampaignRecord;
use putyourlightson\campaign\records\ContactMailingListRecord;

use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;

/**
 * ReportsService
 *
 * @author    PutYourLightsOn
 * @package   Campaign
 * @since     1.0.0
 *
 * @property array $mailingListsChartData
 * @property array $contactsReportData
 * @property array $campaignsReportData
 * @property array $campaignsChartData
 * @property array $mailingListsReportData
 */
class ReportsService extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * Returns campaigns report data
     *
     * @return array
     */
    public function getCampaignsReportData(): array
    {
        // Get all sent campaigns
        $data['campaigns'] = CampaignElement::find()
            ->status(CampaignElement::STATUS_SENT)
            ->all();

        // Get data
        $data['recipients'] = 0;
        $data['opened'] = 0;
        $data['clicked'] = 0;

        /** @var CampaignElement $campaign */
        foreach ($data['campaigns'] as $campaign) {
            $data['recipients'] += $campaign->recipients;
            $data['opened'] += $campaign->opened;
            $data['clicked'] += $campaign->clicked;
        }

        $data['clickThroughRate'] = $data['opened'] ? (float)number_format($data['clicked'] / $data['opened'] * 100, 1) : 0;

        // Get sendouts count
        $data['sendouts'] = SendoutElement::find()->count();

        return $data;
    }

    /**
     * Returns campaigns chart data
     *
     * @return array
     */
    public function getCampaignsChartData(): array
    {
        // Get all sent campaigns
        $data['campaigns'] = CampaignElement::find()
            ->status(CampaignElement::STATUS_SENT)
            ->all();

        // Get interactions
        $data['interactions'] = ContactCampaignModel::INTERACTIONS;

        return $data;
    }

    /**
     * Returns campaign report data
     *
     * @param int
     *
     * @return array
     */
    public function getCampaignReportData(int $campaignId): array
    {
        // Get campaign
        $data['campaign'] = Campaign::$plugin->campaigns->getCampaignById($campaignId);

        // Get sendouts
        $data['sendouts'] = SendoutElement::find()
            ->campaignId($campaignId)
            ->orderBy('sendDate')
            ->all();

        // Check if chart exists
        $data['hasChart'] = \count($this->getCampaignContactActivity($campaignId, null, 1)) > 0;

        return $data;
    }

    /**
     * Returns campaign chart data
     *
     * @param int
     * @param string|null
     *
     * @return array
     */
    public function getCampaignChartData(int $campaignId, string $interval = 'hours'): array
    {
        $data = [];

        // Get first sendout
        /** @var SendoutElement $sendout */
        $sendout = SendoutElement::find()
            ->campaignId($campaignId)
            ->orderBy('sendDate asc')
            ->one();

        if ($sendout === null) {
            return $data;
        }

        // Get date time format ensuring interval is valid
        $format = $this->_getDateTimeFormat($interval);
        if ($format === null) {
            return $data;
        }

        // Get start and end date times
        $startDateTime = $sendout->sendDate->modify('-1 '.$interval);
        $endDateTime = clone $startDateTime;
        $endDateTime->modify('+12 '.$interval);

        // Get contact campaigns within date range
        $contactCampaignRecords = ContactCampaignRecord::find()
            ->where(['campaignId' => $campaignId])
            ->andWhere(Db::parseDateParam('dateCreated', '<'.$endDateTime->format(\DateTime::W3C)))
            ->orderBy('dateCreated asc')
            ->all();

        // Get interactions
        $interactions = ContactCampaignModel::INTERACTIONS;

        // Get activity
        $activity = [];
        foreach ($contactCampaignRecords as $contactCampaignRecord) {
            /** @var ContactCampaignRecord $contactCampaignRecord */
            $dateTime = DateTimeHelper::toDateTime($contactCampaignRecord->dateCreated);
            if ($dateTime > $endDateTime) {
                break;
            }

            foreach ($interactions as $interaction) {
                if ($contactCampaignRecord->$interaction) {
                    $dateTime = DateTimeHelper::toDateTime($contactCampaignRecord->$interaction);
                    $index = $dateTime->format($format['index']);
                    $activity[$interaction][$index] = isset($activity[$interaction][$index]) ? $activity[$interaction][$index] + 1 : 1;
                }
            }
        }

        // Set data
        $data['startDateTime'] = $startDateTime;
        $data['interval'] = $interval;
        $data['format'] = $format;
        $data['interactions'] = $interactions;
        $data['activity'] = $activity;

        return $data;
    }

    /**
     * Returns campaign contact activity
     *
     * @param int
     * @param string|null
     * @param int|null
     *
     * @return ContactActivityModel[]
     */
    public function getCampaignContactActivity(int $campaignId, string $interaction = null, int $limit = 100): array
    {
        // Get contact campaigns
        $query = ContactCampaignRecord::find()
            ->where(['campaignId' => $campaignId])
            ->orderBy('dateUpdated desc')
            ->limit($limit);

        if ($interaction !== null) {
            $query->andWhere(['not', [$interaction => null]]);
        }

        $contactCampaignRecords = $query->all();

        $contactCampaignModels = ContactCampaignModel::populateModels($contactCampaignRecords, false);

        // Return contact activity
        return $this->_getActivity($contactCampaignModels, $interaction, $limit);
    }

    /**
     * Returns campaign links
     *
     * @param int
     * @param int|null
     *
     * @return LinkModel[]
     */
    public function getCampaignLinks(int $campaignId, int $limit = 100): array
    {
        // Get campaign links
        $linkRecords = LinkRecord::find()
            ->where(['campaignId' => $campaignId])
            ->orderBy('clicked desc, clicks desc')
            ->limit($limit)
            ->all();

        return LinkModel::populateModels($linkRecords, false);
    }

    /**
     * Returns campaign locations
     *
     * @param int
     * @param int|null
     *
     * @return array
     */
    public function getCampaignLocations(int $campaignId, int $limit = 100): array
    {
        // Get campaign
        $campaign = Campaign::$plugin->campaigns->getCampaignById($campaignId);

        // Return locations of contact campaigns
        return $this->_getLocations(ContactCampaignRecord::tableName(), ['and', ['campaignId' => $campaignId], ['not', ['opened' => null]]], $campaign->opened, $limit);
    }

    /**
     * Returns campaign devices
     *
     * @param int
     * @param bool
     * @param int|null
     *
     * @return array
     */
    public function getCampaignDevices(int $campaignId, bool $detailed = false, int $limit = 100): array
    {
        // Get campaign
        $campaign = Campaign::$plugin->campaigns->getCampaignById($campaignId);

        // Return device, os and client of contact campaigns
        return $this->_getDevices(ContactCampaignRecord::tableName(), ['campaignId' => $campaignId], $detailed, $campaign->opened, $limit);
    }

    /**
     * Returns contacts report data
     *
     * @return array
     */
    public function getContactsReportData(): array
    {
        // Get interactions
        $interactions = ContactMailingListModel::INTERACTIONS;

        foreach ($interactions as $interaction) {
            $count = ContactMailingListRecord::find()
                ->where(['subscriptionStatus' => $interaction])
                ->count();

            $data[$interaction] = $count;
        }

        // Get contacts data
        $data['active'] = ContactElement::find()
                ->where(['complained' => null, 'bounced' => null])
                ->count();

        $data['complained'] = ContactElement::find()
                ->where(['not', ['complained' => null]])
                ->count();

        $data['bounced'] = ContactElement::find()
                ->where(['not', ['bounced' => null]])
                ->count();

        $data['total'] = $data['active'] + $data['complained'] + $data['bounced'];

        return $data;
    }

    /**
     * Returns contacts activity
     *
     * @param int|null
     *
     * @return array
     */
    public function getContactsActivity(int $limit = 100): array
    {
        // Get recently active contacts
        $contacts = ContactElement::find()
            ->orderBy('lastActivity desc')
            ->limit($limit)
            ->all();

        return $contacts;
    }

    /**
     * Returns contacts locations
     *
     * @param int|null
     *
     * @return array
     */
    public function getContactsLocations(int $limit = 100): array
    {
        // Get total active contacts
        $total = ContactElement::find()
            ->where(['complained' => null, 'bounced' => null])
            ->count();

        // Return locations of contacts
        return $this->_getLocations(ContactRecord::tableName(), [], $total, $limit);
    }

    /**
     * Returns contacts devices
     *
     * @param bool
     * @param int|null
     *
     * @return array
     */
    public function getContactsDevices(bool $detailed = false, int $limit = 100): array
    {
        // Get total active contacts
        $total = ContactElement::find()
            ->where(['complained' => null, 'bounced' => null])
            ->count();

        // Return device, os and client of contacts
        return $this->_getDevices(ContactRecord::tableName(), [], $detailed, $total, $limit);
    }

    /**
     * Returns contact campaigns
     *
     * @param int
     * @param int|null
     *
     * @return ContactActivityModel[]
     */
    public function getContactCampaignActivity(int $contactId, int $limit = 100): array
    {
        // Get contact campaigns
        $contactCampaignRecords = ContactCampaignRecord::find()
            ->where(['contactId' => $contactId])
            ->orderBy('dateUpdated desc')
            ->limit($limit)
            ->all();

        $contactCampaignModels = ContactCampaignModel::populateModels($contactCampaignRecords, false);

        // Return contact activity
        return $this->_getActivity($contactCampaignModels, null, $limit);
    }

    /**
     * Returns contact mailing list activity
     *
     * @param int
     * @param int|null
     *
     * @return ContactActivityModel[]
     */
    public function getContactMailingListActivity(int $contactId, int $limit = 100): array
    {
        // Get mailing lists
        $contactMailingListRecords = ContactMailingListRecord::find()
            ->where(['contactId' => $contactId])
            ->orderBy('dateUpdated desc')
            ->limit($limit)
            ->all();

        $contactMailingListModels = ContactMailingListModel::populateModels($contactMailingListRecords, false);

        // Return contact activity
        return $this->_getActivity($contactMailingListModels, null, $limit);
    }

    /**
     * Returns contact timeline
     *
     * @param int
     * @param int|null
     *
     * @return ContactActivityModel[]
     */
    public function getContactTimeline(int $contactId, int $limit = 100): array
    {
        // Get contact campaigns
        $contactCampaignRecords = ContactCampaignRecord::find()
            ->where(['contactId' => $contactId])
            ->orderBy('dateUpdated desc')
            ->limit($limit)
            ->all();

        $contactCampaignModels = ContactCampaignModel::populateModels($contactCampaignRecords, false);

        // Get mailing lists
        $contactMailingListRecords = ContactMailingListRecord::find()
            ->where(['contactId' => $contactId])
            ->orderBy('dateUpdated desc')
            ->limit($limit)
            ->all();

        $contactMailingListModels = ContactMailingListModel::populateModels($contactMailingListRecords, false);

        // Return contact activity
        $models = array_merge($contactCampaignModels, $contactMailingListModels);

        return $this->_getActivity($models, null, $limit);
    }

    /**
     * Returns mailing lists report data
     *
     * @return array
     */
    public function getMailingListsReportData(): array
    {
        // Get all mailing lists
        $data['mailingLists'] = MailingListElement::findAll();

        // Get data
        $data['subscribed'] = 0;
        $data['unsubscribed'] = 0;
        $data['complained'] = 0;
        $data['bounced'] = 0;

        /** @var MailingListElement $mailingList */
        foreach ($data['mailingLists'] as $mailingList) {
            $data['subscribed'] += $mailingList->getSubscribedCount();
            $data['unsubscribed'] += $mailingList->getUnsubscribedCount();
            $data['complained'] += $mailingList->getComplainedCount();
            $data['bounced'] += $mailingList->getBouncedCount();
        }

        return $data;
    }

    /**
     * Returns mailing lists chart data
     *
     * @return array
     */
    public function getMailingListsChartData(): array
    {
        // Get all mailing lists
        $data['mailingLists'] = MailingListElement::findAll();

        // Get interactions
        $data['interactions'] = ContactMailingListModel::INTERACTIONS;

        return $data;
    }

    /**
     * Returns mailing list report data
     *
     * @param int
     *
     * @return array
     */
    public function getMailingListReportData(int $mailingListId): array
    {
        // Get mailing list
        $data['mailingList'] = Campaign::$plugin->mailingLists->getMailingListById($mailingListId);

        // Get sendouts
        $data['sendouts'] = SendoutElement::find()
            ->mailingListId($mailingListId)
            ->orderBy('sendDate')
            ->all();

        // Get first contact mailing list
        $contactMailingListRecord = ContactMailingListRecord::find()
            ->where(['mailingListId' => $mailingListId])
            ->orderBy('dateCreated asc')
            ->one();

        // Check if chart exists
        $data['hasChart'] = ($contactMailingListRecord !== null);

        return $data;
    }

    /**
     * Returns mailing list chart data
     *
     * @param int
     * @param string|null
     *
     * @return array
     */
    public function getMailingListChartData(int $mailingListId, string $interval = 'days'): array
    {
        $data = [];

        // Get mailing list
        $mailingList = Campaign::$plugin->mailingLists->getMailingListById($mailingListId);

        // Get date time format ensuring interval is valid
        $format = $this->_getDateTimeFormat($interval);
        if ($format === null) {
            return $data;
        }

        // Get start and end date times
        $startDateTime = $mailingList->dateCreated->modify('-1 '.$interval);
        $endDateTime = clone $startDateTime;
        $endDateTime->modify('+12 '.$interval);

        // Get contact mailing lists within date range
        $contactMailingListRecords = ContactMailingListRecord::find()
            ->where(['mailingListId' => $mailingListId])
            ->andWhere(Db::parseDateParam('dateCreated', '<'.$endDateTime->format(\DateTime::W3C)))
            ->orderBy('dateCreated asc')
            ->all();

        // Get interactions
        $interactions = ContactMailingListModel::INTERACTIONS;

        // Get activity
        $activity = [];
        foreach ($contactMailingListRecords as $contactMailingListRecord) {
            /** @var ContactMailingListRecord $contactMailingListRecord */
            $dateTime = DateTimeHelper::toDateTime($contactMailingListRecord->dateCreated);
            if ($dateTime > $endDateTime) {
                break;
            }

            foreach ($interactions as $interaction) {
                if ($contactMailingListRecord->$interaction) {
                    $dateTime = DateTimeHelper::toDateTime($contactMailingListRecord->$interaction);
                    $index = $dateTime->format($format['index']);
                    $activity[$interaction][$index] = isset($activity[$interaction][$index]) ? $activity[$interaction][$index] + 1 : 1;
                }
            }
        }

        // Set data
        $data['startDateTime'] = $startDateTime;
        $data['interval'] = $interval;
        $data['format'] = $format;
        $data['interactions'] = $interactions;
        $data['activity'] = $activity;

        return $data;
    }

    /**
     * Returns mailing list contact activity
     *
     * @param int
     * @param string|null
     * @param int|null
     *
     * @return ContactMailingListModel[]
     */
    public function getMailingListContactActivity(int $mailingListId, string $interaction = null, int $limit = 100): array
    {
        // Get contact mailing lists
        $contactMailingListRecords = ContactMailingListRecord::find()
            ->where(['mailingListId' => $mailingListId])
            ->orderBy('dateUpdated desc')
            ->limit($limit)
            ->all();

        $contactMailingListModels = ContactMailingListModel::populateModels($contactMailingListRecords, false);

        // Return contact activity
        return $this->_getActivity($contactMailingListModels, $interaction, $limit);
    }

    /**
     * Returns mailing list locations
     *
     * @param int
     * @param int|null
     *
     * @return array
     */
    public function getMailingListLocations(int $mailingListId, int $limit = 100): array
    {
        // Get mailing list
        $mailingList = Campaign::$plugin->mailingLists->getMailingListById($mailingListId);

        // Return locations of contact mailing lists
        return $this->_getLocations(ContactMailingListRecord::tableName(), ['mailingListId' => $mailingListId], $mailingList->getSubscribedCount(), $limit);
    }

    /**
     * Returns mailing list devices
     *
     * @param int
     * @param bool
     * @param int|null
     *
     * @return array
     */
    public function getMailingListDevices(int $mailingListId, bool $detailed = false, int $limit = 100): array
    {
        // Get mailing list
        $mailingList = Campaign::$plugin->mailingLists->getMailingListById($mailingListId);

        // Return device, os and client of contact mailing lists
        return $this->_getDevices(ContactMailingListRecord::tableName(), ['mailingListId' => $mailingListId], $detailed, $mailingList->getSubscribedCount(), $limit);
    }

    // Private Methods
    // =========================================================================

    /**
     * Returns activity
     *
     * @param array
     * @param string|null
     * @param int|null
     *
     * @return ContactActivityModel[]
     */
    private function _getActivity(array $models, string $interaction = null, int $limit = 100): array
    {
        $activity = [];

        foreach ($models as $model) {
            /** @var ContactCampaignModel|ContactMailingListModel $model */
            $interactionTypes = ($interaction !== null AND \in_array($interaction, $model::INTERACTIONS, true)) ? [$interaction] : $model::INTERACTIONS;

            foreach ($interactionTypes as $interactionType) {
                if ($model->$interactionType !== null) {
                    $contactActivityModel = new ContactActivityModel();
                    $contactActivityModel->model = $model;
                    $contactActivityModel->interaction = $interactionType;
                    $contactActivityModel->date = $model->$interactionType;
                    $contactActivityModel->links = $interactionType == 'clicked' ? $model->getLinks() : [];

                    $contactActivityModel->count = 1;
                    if ($interactionType == 'opened') {
                        $contactActivityModel->count = $model->opens;
                    }
                    else if ($interactionType == 'clicked') {
                        $contactActivityModel->count = $model->clicks;
                    }

                    $activity[$model->$interactionType.' '.$interactionType] = $contactActivityModel;
                }
            }
        }

        // Sort by key in reverse order
        krsort($activity);

        // Enforce the limit
        $activity = \array_slice($activity, 0, $limit);

        return $activity;
    }

    /**
     * Returns locations
     *
     * @param string
     * @param array
     * @param int
     * @param int|null
     *
     * @return array
     */
    private function _getLocations(string $table, array $conditions, int $total, int $limit = 100): array
    {
        $countArray = [];

        $subQuery = (new Query())
            ->select(['MAX(id) as id', 'country', 'COUNT(*) AS count'])
            ->from($table)
            ->where($conditions)
            ->groupBy('country');

        $results = (new Query())
            ->select(['subquery.country', 'subquery.count', 't.geoIp'])
            ->from(['subquery' => $subQuery])
            ->innerJoin($table.' t', 't.id = subquery.id')
            ->all();

        // Set default unknown count
        $unknownCount = 0;

        foreach ($results as $key => &$result) {
            // Increment and unset unknown results
            if (empty($result['country'])) {
                $unknownCount += $result['count'];
                unset($results[$key]);
                continue;
            }

            // Get lower case country code
            $geoIp = $result['geoIp'] ? json_decode($result['geoIp'], true) : [];
            $countryCode = $geoIp['country_code'] ?? '';
            $countryCode = strtolower($countryCode);

            $result['countryCode'] = $countryCode;
            $result['countRate'] = $total ? number_format($result['count'] / $total * 100, 1) : 0;

            $countArray[] = $result['count'];
        }

        // If there is an unknown count then add it to results
        if ($unknownCount > 0) {
            $results[] = [
                'country' => '',
                'countryCode' => '',
                'count' => $unknownCount,
                'countRate' => $total ? number_format($unknownCount / $total * 100, 1) : 0,
            ];
            $countArray[] = $unknownCount;
        }

        // Sort results by count array descending
        array_multisort($countArray, SORT_DESC, $results);

        // Enforce the limit
        $results = \array_slice($results, 0, $limit);

        return $results;
    }

    /**
     * Returns devices
     *
     * @param string
     * @param array
     * @param bool
     * @param int
     * @param int|null
     *
     * @return array
     */
    private function _getDevices(string $table, array $conditions, bool $detailed, int $count, int $limit = 100): array
    {
        $countArray = [];

        $fields = $detailed ? ['device', 'os', 'client'] : ['device'];

        $results = (new Query())
            ->select(array_merge($fields, ['COUNT(*) AS count']))
            ->from($table)
            ->where($conditions)
            ->andWhere(['not', ['device' => null]])
            ->groupBy($fields)
            ->all();

        foreach ($results as &$result) {
            $result['countRate'] = $count ? number_format($result['count'] / $count * 100, 1) : 0;
            $countArray[] = $result['count'];
        }

        // Sort results by count array descending
        array_multisort($countArray, SORT_DESC, $results);

        // Enforce the limit
        $results = \array_slice($results, 0, $limit);

        return $results;
    }

    /**
     * Returns date time format
     *
     * @param string
     *
     * @return array
     */
    private function _getDateTimeFormat(string $interval): array
    {
        // Get date and time formats
        $longDateFormat = Craft::$app->getLocale()->getDateFormat('long', 'php');
        $shortDateFormat = Craft::$app->getLocale()->getDateFormat('short', 'php');
        $timeFormat = Craft::$app->getLocale()->getDateTimeFormat('long', 'php');

        $formats = [
            'minutes' => [
                'index' => str_replace(':s', '', $timeFormat),
                'label' => 'H:i',
            ],
            'hours' => [
                'index' => str_replace(['i', ':s'], ['00', ''], $timeFormat),
                'label' => 'H:00',
            ],
            'days' => [
                'index' => $longDateFormat,
                'label' => substr($shortDateFormat, 0, 3),
            ],
            'weeks' => [
                'index' => $longDateFormat,
                'label' => substr($shortDateFormat, 0, 3),
            ],
            'months' => [
                'index' => str_replace('j ', '', $longDateFormat),
                'label' => str_replace('j/', '', $shortDateFormat),
            ],
            'years' => [
                'index' => str_replace(['j ', 'F '], '', $longDateFormat),
                'label' => str_replace(['j/', 'n/'], '', $shortDateFormat),
            ],
        ];

        return $formats[$interval] ?? [];
    }
}