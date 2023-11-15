=== Grautic: Gravity Forms + Mautic ===
Contributors: k2kirov, vmfork
Tags: forms, emails, subscribers, mautic, gravity forms
Requires at least: 5.2
Tested up to: 6.0
Requires PHP: 7.3
Stable tag: 2.2.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integrates Gravity Forms with Mautic, allowing form submissions to be automatically sent to your Mautic contact list and segments.

== Description ==

Integrate your Gravity Forms with Mautic to send submissions with email fields to your contacts and segments.

== Installation ==

1. Upload the extracted contents of `gravityforms-mautic-addon.zip` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Forms > Settings > Mautic and add your Mautic credentials

== Frequently Asked Questions ==

= Why can't I create a feed? =

Make sure you have set a valid username and password in the global Mautic settings by going to Forms > Settings > Mautic. There should be a green checkmark next to the fields if the credentials are valid.

= Why the field dropdowns in the mapping settings are empty? =

* To map the **Email** you need to have a field of type [Email](https://docs.gravityforms.com/email/) or [Hidden](https://docs.gravityforms.com/hidden/).
* To map the **First Name** and **Last Name** you need to have a field of type [Name](https://docs.gravityforms.com/name/), [Text](https://docs.gravityforms.com/text-field/), or [Hidden](https://docs.gravityforms.com/hidden/)

= The field dropdown options in the mapping settings are blank but selectable, what's going on? =

The field dropdowns show the [Field Label](https://docs.gravityforms.com/common-field-settings/#field-label) or [Admin Field Label](https://docs.gravityforms.com/common-field-settings/#admin-field-label), so make sure you have either of those set up in your fields. Or both, it's also a good practice for accessibility!

== Advanced ==

You can customize the contact data sent to Mautic in the entry submission context with this hook:

`apply_filters( 'gragrid_contact_params', array $contact_params, array $entry, array $form )`

- `$contact_params` (array): Contact parameters, includes first name, email, custom fields, etc.
- `$entry` (array): The form entry that was just created.
- `$form` (array): The current form, the origin of the submission.

== Changelog ==

= 1.0.0 =
* Initial release
