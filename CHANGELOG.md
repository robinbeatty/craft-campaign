# Release Notes for Campaign

## 2.11.1 - Unreleased

### Fixed

- Fixed a bug in which the element count was not appearing for campaigns and mailing lists when there were no results.

## 2.11.0 - 2023-11-28

### Added

- Added the ability to bulk subscribe and unsubscribe contacts to and from mailing lists in the contact index page ([#418](https://github.com/putyourlightson/craft-campaign/issues/418)).
- Added the ability to configure a custom queue component via `config/app.php` to use when running queue jobs.
- Added an `importJobPriority` config setting that determines what priority to give import jobs.

### Fixed

- Fixed a bug in which a default contact was not being shown in test contacts on the campaign edit page ([#437](https://github.com/putyourlightson/craft-campaign/issues/437)).
- Fixed a bug in which saving a contact could throw an exception if the contact record existed but the contact was not active.

## 2.10.0 - 2023-11-16

### Added

- Added a new “validate webhook request” field setting.
- Added a webhook controller action for [MailerSend](https://www.mailersend.com/) and signature key verification with a new `mailersendWebhookSigningSecret` setting ([#415](https://github.com/putyourlightson/craft-campaign/issues/415)).
- Added validation to the SendGrid webhook controller action with a new `sendgridWebhookVerificationKey` setting.
- Added the `importJobTtr` and `syncJobTtr` config settings ([#432](https://github.com/putyourlightson/craft-campaign/issues/432)).

### Changed

- Updated the SendGrid webhook event names.
- Adding or removing test contacts no longer shows an unsaved changes notification ([#437](https://github.com/putyourlightson/craft-campaign/issues/437)).

## 2.9.4 - 2023-11-07

### Fixed

- Fixed a bug in which importing Campaign elements using the Feed Me plugin could throw errors when the queue job was run via the console ([#428](https://github.com/putyourlightson/craft-campaign/issues/428)).
- Fixed a bug in which importing a user group could throw an exception in some scenarios ([#431](https://github.com/putyourlightson/craft-campaign/issues/431)).

## 2.9.3 - 2023-10-31

### Fixed

- Fixed a bug in which an exception could be thrown on the sendouts screen ([#429](https://github.com/putyourlightson/craft-campaign/issues/429)).

## 2.9.2 - 2023-10-26

### Fixed

- Fixed a bug in which selecting a user group to import was throwing an exception ([#425](https://github.com/putyourlightson/craft-campaign/issues/425)).
- Fixed a bug in which saving a sendout with emojis in the subject could throw an error when adding the sendout to the queue  ([#426](https://github.com/putyourlightson/craft-campaign/issues/426)).

## 2.9.1 - 2023-10-17

### Changed

- Disabled validation when saving elements in specific cases.
- Unsubscribed, complained and bounced counts are now included when syncing campaign reports.

## 2.9.0 - 2023-10-09

### Added

- Added an info message and a new campaign type button to the campaign index page when no campaign types exist.
- Added an info message and a new mailing list type button to the mailing list index page when no mailing list types exist.
- Added the `sendoutJobPriority` config setting ([#422](https://github.com/putyourlightson/craft-campaign/issues/422)).

### Changed

- Sendouts are now sent to contacts in a deterministic order ([#423](https://github.com/putyourlightson/craft-campaign/issues/423)).

### Fixed

- Fixed the email settings being overwritten when contact settings were saved ([#421](https://github.com/putyourlightson/craft-campaign/issues/421)).
- Fixed the status filter for campaigns, mailing lists and contacts selectable in sendouts ([#419](https://github.com/putyourlightson/craft-campaign/issues/419)).

## 2.8.7 - 2023-09-25

### Fixed

- Fixed relation field values not saving in Craft 4.5.4 and above ([#414](https://github.com/putyourlightson/craft-campaign/issues/414)).

## 2.8.6 - 2023-09-20

### Changed

- Improvements to the French translation.

### Fixed

- Fixed a bug in which Campaign, Contact and Mailing List element types were not being registered by the Feed Me plugin ([#412](https://github.com/putyourlightson/craft-campaign/issues/412)).

## 2.8.5 - 2023-08-21

### Added

- Added the `has a value` and `is empty` options to legacy segment conditions ([#407](https://github.com/putyourlightson/craft-campaign/issues/407)).

## 2.8.4 - 2023-08-15

### Fixed

- Fixed a bug in which clicking an unsubscribe link in singular sendouts was causing an error, since singular sendouts are not sent to mailing lists ([#405](https://github.com/putyourlightson/craft-campaign/issues/405)).

## 2.8.3 - 2023-07-17

### Changed

- Logged messages now include the event that was passed into a webhook when a failure is encountered ([#398](https://github.com/putyourlightson/craft-campaign/issues/398)).

### Fixed

- Fixed a bug that was causing errors when updating search indexes if a sendout’s subject was not set ([#397](https://github.com/putyourlightson/craft-campaign/issues/397)).
- Fixed a bug that was throwing an exception if the Feed Me package was removed but the plugin was still installed ([#400](https://github.com/putyourlightson/craft-campaign/issues/400)).

## 2.8.2 - 2023-06-12

### Fixed

- Fixed a bug in which Campaign dashboard widgets were throwing exceptions when a date range was set ([#396](https://github.com/putyourlightson/craft-campaign/issues/396)).

## 2.8.1 - 2023-06-08

### Fixed

- Fixed a bug in which Feed Me imports were not working when the queue was being run via console requests ([#395](https://github.com/putyourlightson/craft-campaign/issues/395)).

## 2.8.0 - 2023-06-01

### Added

- Added the ability to import campaigns, contacts and mailing lists using Feed Me ([#395](https://github.com/putyourlightson/craft-campaign/issues/395)).
- Added logging of sendout batches and when a sendout completes ([#385](https://github.com/putyourlightson/craft-campaign/issues/385)).

### Changed

- Contact field names now include info modals with instructions in the CSV file import field ([#392](https://github.com/putyourlightson/craft-campaign/issues/392)).

## 2.7.2 - 2023-05-26

### Changed

- Errors when syncing users to contacts are now logged.

### Fixed

- Fixed a bug in which custom field values were not being imported when importing contacts via CSV files ([#390](https://github.com/putyourlightson/craft-campaign/issues/390)).

## 2.7.1 - 2023-05-24

### Changed

- Sendout previews now include template variables for the current sendout as well as a dummy contact ([#386](https://github.com/putyourlightson/craft-campaign/issues/386)).
- Webhooks now return a successful response when receiving the email address of a contact that does not exist ([#388](https://github.com/putyourlightson/craft-campaign/issues/388)).

## 2.7.0 - 2023-05-13

### Added

- Added the ability to import contacts into a mailing list with a status of unsubscribed ([#384](https://github.com/putyourlightson/craft-campaign/issues/384)).

### Changed

- Anchor hashtags are now forced to the end of URLs when appending query string parameters ([#383](https://github.com/putyourlightson/craft-campaign/issues/383)).
- Changed the user permission for managing contacts to only show a note about the primary site if multiple sites exist.

## 2.6.2 - 2023-05-12

### Fixed

- Fixed a bug in which non-admin users were not allowed to edit contacts even with the correct permissions granted ([#378](https://github.com/putyourlightson/craft-campaign/issues/378)).

## 2.6.1 - 2023-04-28

### Added

- Added the ability to import options fields (multi-select, checkboxes, etc.) into contact fields ([#380](https://github.com/putyourlightson/craft-campaign/issues/380)).

## 2.6.0 - 2023-04-04

### Added

- Added messaging that explains why charts are not appearing in reports.

### Changed

- Campaign reports are no longer synced when the `enableAnonymousTracking` setting is enabled ([#371](https://github.com/putyourlightson/craft-campaign/issues/371)).
- Contact activity is hidden when the `enableAnonymousTracking` setting is enabled ([#371](https://github.com/putyourlightson/craft-campaign/issues/371)).
- Renamed the “Manage Reports” permission to “View Reports”.
- Users can now only edit contacts when they have edit permission for the primary site.

### Fixed

- Fixed a bug that was showing reports for the primary site even if the user did not have permission to access that site in the control panel ([#373](https://github.com/putyourlightson/craft-campaign/issues/373)).
- Fixed a bug that was throwing an exception when all contacts were being sorted by subscription status ([#374](https://github.com/putyourlightson/craft-campaign/issues/374)).

## 2.5.5 - 2023-02-24

### Fixed

- Fixed a bug that was preventing charts from appearing in report pages.

## 2.5.4 - 2023-02-23

### Added

- Added compatibility with LitEmoji v4 which is required by Craft 4.4.0.

### Fixed

- Fixed the check for PHP memory usage in sendout jobs.

## 2.5.3 - 2023-02-03

### Changed

- The front-end subscribe and unsubscribe forms now accept an optional `siteId` parameter ([#363](https://github.com/putyourlightson/craft-campaign/issues/363)).

## 2.5.2 - 2023-01-24

### Fixed

- Fixed unauthorised access to some settings pages in environments where administrative changes are disallowed.

## 2.5.1 - 2023-01-17

### Changed

- The translatable symbol no longer appears next to element title fields.

## 2.5.0 - 2023-01-17

### Added

- Added the ability to hide the title field in campaigns and have titles generated dynamically ([#355](https://github.com/putyourlightson/craft-campaign/issues/355)).
- Added the ability to hide the title field in sendouts and have titles generated from the subject ([#356](https://github.com/putyourlightson/craft-campaign/issues/356)).

### Changed

- The sendout title and subject fields are now autopopulated from the campaign title if they are empty ([#356](https://github.com/putyourlightson/craft-campaign/issues/356)).

## 2.4.2 - 2023-01-13

### Fixed

- Fixed a bug in which some stats in the mailing list dashboard widget were not being counted.

## 2.4.1 - 2023-01-13

### Fixed

- Fixed a bug in which the utilities page was throwing an exception ([#357](https://github.com/putyourlightson/craft-campaign/issues/357)).

## 2.4.0 - 2023-01-12

### Added

- Added the “Campaign Stats” dashboard widget ([#107](https://github.com/putyourlightson/craft-campaign/issues/107)).
- Added the “Mailing List Stats” dashboard widget ([#107](https://github.com/putyourlightson/craft-campaign/issues/107)).
- Added “Click Rate” as an available column in the campaign element index page.
- Added “Open Rate” to campaign reports and as an available column in the campaign element index page ([#354](https://github.com/putyourlightson/craft-campaign/issues/354)).
- Added “Click Rate” as an available column in the campaign element index page.
- Added the `campaign/reports/sync` console command that syncs campaign reports.

### Fixed

- Fixed when the view action is available on campaign index pages.

## 2.3.2 - 2023-01-02

### Changed

- Changed the sendout “Pause” button to read “Pause and Edit” ([#352](https://github.com/putyourlightson/craft-campaign/issues/352)).

### Fixed

- Fixed the “View all” link in the contacts tab of the mailing list edit page for Craft 4.3.0 and above.

## 2.3.1 - 2022-12-10

### Changed

- The access utility permission was removed, in favour of Craft's own utility permission.

### Fixed

- Fixed a bug in which an error could be thrown on the imports page if imported mailing lists no longer existed ([#349](https://github.com/putyourlightson/craft-campaign/issues/349)).
- Fixed a bug in which errors could be thrown on reports pages if elements no longer existed ([#349](https://github.com/putyourlightson/craft-campaign/issues/349)).
- Fixed a bug in which sendout jobs could fail when run via console requests.

## 2.3.0 - 2022-12-05

### Added

- Added a new `ContactElement::getIsSubscribedTo()` method.

### Changed

- Rendered HTML templates now explicitly exclude any control panel asset bundles ([#347](https://github.com/putyourlightson/craft-campaign/issues/347)).

### Fixed

- Fixed a bug in which draft contacts were incorrectly being counted as expected recipients.
- Fixed a bug in which custom fields were not being validated in front-end contact subscribe forms ([#348](https://github.com/putyourlightson/craft-campaign/issues/348)).

## 2.2.3 - 2022-11-25

### Changed

- Contact imports now attempt to JSON decode imported values for relation fields ([#345](https://github.com/putyourlightson/craft-campaign/issues/345)).
- Search indexes are now updated only after contacts have finished being imported, rather that than once per contact ([#345](https://github.com/putyourlightson/craft-campaign/issues/345)).

### Fixed

- Fixed the updated column in the import index view.

## 2.2.2 - 2022-11-22

### Changed

- Contacts can now be subscribed to and unsubscribed from mailing lists when in a draft state ([#343](https://github.com/putyourlightson/craft-campaign/issues/343)).
- The email field now outputs a link to a contact if one already exists with the same email address during validation ([#343](https://github.com/putyourlightson/craft-campaign/issues/343)).

## 2.2.1 - 2022-11-07

### Changed

- Improved the performance of report pages ([#340](https://github.com/putyourlightson/craft-campaign/issues/340)).
- Changed the webhook controller action responses to ensure that correct status codes are sent (❤️@brandonkelly).
- Test requests from Mailgun now return a success response.

### Fixed

- Fixed a bug in which the Mailgun webhook controller action was not processing requests correctly ([#341](https://github.com/putyourlightson/craft-campaign/issues/341)).
- Fixed a bug in which the webhook controller actions could fail for singular sendouts.
- Fixed a bug in which some information was missing from reports.

## 2.2.0 - 2022-10-28

### Added

- Added the ability to use `{% html %}`, `{% css %}` and `{% js %}` tags in campaign templates.

### Fixed

- Fixed a bug in which Yii block comments could be unintentionally left over in rendered campaign templates ([#337](https://github.com/putyourlightson/craft-campaign/issues/337)).

## 2.1.17 - 2022-10-28

### Fixed

- Fixed a bug in which the unsubscribe webhook action could throw an exception ([#339](https://github.com/putyourlightson/craft-campaign/issues/339)).

## 2.1.16 - 2022-10-27

### Fixed

- Fixed all remaining uninitialized typed properties, as a precaution.

## 2.1.15 - 2022-10-27

### Fixed

- Fixed a missed uninitialized typed property that was causing verification links to fail ([#338](https://github.com/putyourlightson/craft-campaign/issues/338)).

## 2.1.14 - 2022-10-25

### Fixed

- Fixed a bug in which typed properties were being accessed before initialization, caused by a [breaking change](https://github.com/yiisoft/yii2/issues/19546#issuecomment-1291280606) in Yii 2.0.46.

## 2.1.13 - 2022-10-25

### Fixed

- Fixed a bug in which typed properties were being accessed before initialization.

## 2.1.12 - 2022-10-21

### Fixed

- Fixed a bug in which an exception was thrown when viewing recurring sendouts ([#336](https://github.com/putyourlightson/craft-campaign/issues/336)).

## 2.1.11 - 2022-10-18

### Fixed

- Fixed a bug in which inconsistencies could occur in campaign reports and added a migration to sync campaign report data ([#232](https://github.com/putyourlightson/craft-campaign/issues/232), [#285](https://github.com/putyourlightson/craft-campaign/issues/285)).

## 2.1.10 - 2022-10-17

### Fixed

- Fixed a bug in which saving sendout settings was throwing an exception when one or more of the fields were left blank ([#334](https://github.com/putyourlightson/craft-campaign/issues/334)).

## 2.1.9 - 2022-10-12

### Fixed

- Fixed a bug in which blocked contacts were being considered as pending contacts, meaning that sendouts could hang during sending ([#324](https://github.com/putyourlightson/craft-campaign/issues/324)).

## 2.1.8 - 2022-10-10

### Fixed

- Fixed a caching issue when applying project config changes to campaign types and mailing list types ([#332](https://github.com/putyourlightson/craft-campaign/issues/332)).

## 2.1.7 - 2022-09-16

### Fixed

- Fixed a bug in which the edit action was available for sendouts that were no longer modifiable ([#328](https://github.com/putyourlightson/craft-campaign/issues/328)).
- Fixed a bug in which an exception was thrown when previewing a sendout in which the campaign no longer exists ([#329](https://github.com/putyourlightson/craft-campaign/issues/329)).

## 2.1.6 - 2022-09-16

### Fixed

- Fixed a bug in which sending test emails of campaigns only worked for campaigns in the default site ([#327](https://github.com/putyourlightson/craft-campaign/issues/327)).

## 2.1.5 - 2022-08-28

### Fixed

- Fixed an error that could be thrown if the `set_time_limit()` function was undefined ([#322](https://github.com/putyourlightson/craft-campaign/issues/322)).

## 2.1.4 - 2022-08-07

### Fixed

- Fixed a bug that prevented the mailer transport defined in the Campaign email settings from overriding the Craft email settings ([#319](https://github.com/putyourlightson/craft-campaign/issues/319)).

## 2.1.3 - 2022-08-02

### Fixed

- Fixed an error that occurred when creating contacts in the control panel.

## 2.1.2 - 2022-07-25

### Fixed

- Fixed a bug when subscribing users via front-end forms.

## 2.1.1 - 2022-07-22

### Added

- Added a `fieldValues` parameter to the `FormsService::createAndSubscribeContact` method.

## 2.1.0 - 2022-07-22

### Added

- Added a `createAndSubscribeContact` method to `FormsService` for easier integration from other plugins and modules.

## 2.0.6 - 2022-07-18

### Fixed

- Fixed a bug in which an exception could be thrown if the user agent was unavailable when detecting device type.

## 2.0.5 - 2022-07-05

### Changed

- Tweaked plugin icon to fit better in control panel.

### Fixed

- Fixed subscription status translations.

## 2.0.4 - 2022-06-24

### Changed

- Removed the pruning of deleted fields according to the precedent set in [craftcms/cms#11054](https://github.com/craftcms/cms/discussions/11054#discussioncomment-2881106).

### Fixed

- Fixed an issue with viewing sendouts that have been sent in Craft 4.0.4 and above ([#316](https://github.com/putyourlightson/craft-campaign/issues/316)).

## 2.0.3 - 2022-06-21

### Fixed

- Fixed an issue with the contact index page when database tables contained a prefix.

## 2.0.2 - 2022-06-21

### Fixed

- Fixed the unique email validation when saving contacts.

## 2.0.1 - 2022-06-03

### Changed

- Improved the UI of sendout previews.
- Made the `matomo/device-detector` package requirement more flexible.

## 2.0.0 - 2022-05-04

> {warning} Support for reCAPTCHA version 2 has been removed, use version 3 instead. The `subscribeVerificationSuccessTemplate` setting has been removed, use the `subscribeSuccessTemplate` setting instead.

### Added

- Added compatibility with Craft 4.
- Added a new “Singular” sendout type to the Pro edition, for sending campaigns to individual contacts ([#263](https://github.com/putyourlightson/craft-campaign/issues/263)).
- Added a condition builder field to the sendout schedule for automated and recurring sendout types ([#305](https://github.com/putyourlightson/craft-campaign/issues/305)).
- Added the field layout designer to campaign types, mailing list types and contact layouts ([#163](https://github.com/putyourlightson/craft-campaign/issues/163), [#198](https://github.com/putyourlightson/craft-campaign/issues/198), [#269](https://github.com/putyourlightson/craft-campaign/issues/269)).
- Added autosaving drafts to campaigns, contacts, mailing lists, segments and sendouts.
- Added revisions to campaigns ([#301](https://github.com/putyourlightson/craft-campaign/issues/301)).
- Added a “Duplicate” action to campaigns, mailing lists, segments and sendouts ([#292](https://github.com/putyourlightson/craft-campaign/issues/292)).
- Added condition settings to the campaigns, contacts and mailing lists relation fields.
- Added user group permissions for campaign types and mailing list types.
- Added the ability to view disabled campaigns using a token URL.
- Added a contact condition builder to regular segment types, that should be used going forward since `legacy` and `template` segment types will be removed in Campaign 3.
- Added a “Campaign Activity” condition rule for segmenting by contacts who have opened or clicked a link in any or a specific campaign ([#244](https://github.com/putyourlightson/craft-campaign/issues/244)).
- Added a “Default Notification Contacts” field to sendout settings.
- Added an “Export to CSV” button to all datatables in reports ([#245](https://github.com/putyourlightson/craft-campaign/issues/245)).
- Added the `enableAnonymousTracking` setting, which prevents tracking of opens and clicks ([#115](https://github.com/putyourlightson/craft-campaign/issues/115)).
- Added the `campaign/reports/anonymize` console controller that anonymizes all previously collected personal data.
- Added a list of failed contacts to sendouts that have failures ([#311](https://github.com/putyourlightson/craft-campaign/issues/311)).
- Added a link to view all contacts from the mailing list edit page ([#282](https://github.com/putyourlightson/craft-campaign/issues/282)).

### Changed

- All `forms` controller actions now return a `400` HTTP status for JSON responses when unsuccessful.
- Improved the UI and security of links to external sites.
- Exports now include all contacts in the selected mailing lists, as well as columns for mailing list, subscription status and subscribed date ([#302](https://github.com/putyourlightson/craft-campaign/issues/302)).
- Verification emails are now sent in HTML and plaintext format ([#303](https://github.com/putyourlightson/craft-campaign/issues/303)).
- Renamed `regular` segment types to `legacy` segment types, which are being maintained because they provide functionality that the contact condition builder does not yet provide, but which will be removed in Campaign 3.
- Renamed the `maxSendFailsAllowed` config setting to `maxSendFailuresAllowed`.
- Replaced the `Log To File` helper package with a custom Monolog log target.
- Replaced all instances of `AdminTable` with `VueAdminTable`.
- Removed the `SettingsService` class. Use the `SettingsHelper` class instead.

### Removed

- Removed support for reCAPTCHA version 2, leaving support for version 3 only.
- Removed the `subscribeVerificationSuccessTemplate` setting from the mailing list type settings page. Use the `subscribeSuccessTemplate` setting instead.
