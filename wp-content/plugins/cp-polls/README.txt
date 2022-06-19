=== CP Polls ===
Contributors: codepeople
Donate link: http://wordpress.dwbooster.com/forms/cp-polls
Tags: poll,polls,form,forms,voting,plugin,survey,post,vote,votes,statistics,stats,feedback,evaluation
Requires at least: 3.0.5
Tested up to: 4.4
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create classic polls and advanced polls with dependant questions.

== Description ==

CP Polls features:

* Classic one-click radio-button polls	
* Advanced polls with dependant questions
* Export results to Excel / CSV
* Printable list of votes
* Visual drag and drop form builder	
* Anti-spam captcha	
* Field validation
* Graphic reports
* Printable reports
* Email notifications
* Automatic email reports
* ... and more features (see below)

With **CP Polls** you can publish a poll into a page/post and optionally display statistics of the results. You can receive email notifications every time a vote is added or opt to receive Excel reports periodically.

The Polls can have dependant questions, this means that some questions are displayed depending of the selection made on other questions.

= More about the Main Features: =

* **Votes can be limited to one per user:** Poll votes can be limited to one per user based in cookies or IP verification. Can be also set to accept unlimited votes.
* **Interactive questions:** The questions can be shown or hidden depending of the selection made on previous questions. 
* **All languages supported:** Visually configurable/editable for any language. 
* **Email delivery / notifications:** The poll votes can be by email to one or more email addresses.
* **Poll votes saved into the database:** For keeping a record of the received poll votes, generate statistics and export data.
* **Printable list of votes:** Get the list of votes received from the poll form within a selected date range and print it.
* **Export data to CSV/Excel:** Export the polls data to a standard format that can be used by other applications.
* **Automatic reports:** Provide automatic reports of the poll form usage and votes. Report of daily votes and accumulative hourly report. Printable reports for specific fields into the poll. Helps you to understand your data.
* **Automatic email reports:** Automatic reports sent to the indicated email addresses on a regular period.
* **Form Validation:** Set validation rules to avoid getting empty answers.
* **Anti-spam protection:** Built-it captcha anti-spam protection. 
* **Drag and drop poll form builder:** A basic and easy to use poll form builder for customizing the poll fields and form validation.
* **Customizable email messages:** Specify the text of the email notifications. Supports both plain text emails and HTML formatted emails.
* **Multi language support:** The poll form builder allows to enter the field labels and validations in any language. It supports special characters.

= Votes List =

The poll votes list helps to check the previous votes and print or export them. Includes a search/filter form with the following options:

* **Search for:** Search for a text into the poll votes.
* **From ... to:** Date interval to be included in the list/reports.
* **Item:** You can have more than one poll form. Select here if you want to get the results of a specific poll form or from all poll forms.
* **Filter:** Shows the list according to the selected filters/options.
* **Export to CSV:** Export the CSV data according to the selected filters/options.

The CSV file will contain a first row with the field names and the next rows will contain one poll vote per row, with one for field on each column. This way you can easily import the data from other applications or just select the columns/fields that you need. A CSV file can be opened and managed using Excel.

A print button below the list provides the poll votes in a printable format.

= The Poll Reports =

The reports section lets you **analyze the use of the poll forms** and the data entered into them. The first section of the reports is a filter section similar to the one that appears in the poll votes page. Below the filters section there are three graphical reports:

* **Votes per day:** The report will display in point-lines graphic how many poll votes have been received each day in the selected date range. This report can be used to evaluate the usage peaks and measure the impact of marketing actions.

* **Votes per hour:** The report will display in a point-lines graphic how many poll votes are received on each hour of the date; this is for the total poll votes in the selected date range. This report can be used for checking peak hours and focus the marketing actions on those hours.

* **Report of values for a selected field:** Select any of the poll fields and other information fields (like date, IP address, hours) to get a report of how many times each value have been selected.

A print button at the end of the page can be used to print the report of the values for the selected poll field in a printer-friendly format.

= Automatic Email Reports =

The CP Polls plugin allows the setup of two types of automatic (periodical) email reports:

* **Global Email Reports:** Can be setup below the list of polls. This report sends a report with the new poll votes of all polls every the specified number of days.

* **Poll Email Reports:** Can be setup on the settings page of each poll. This report sends a report with the new poll votes of the related poll every the specified number of days.

The reports are attached in a CSV / Excel file into the emails. In both cases the destination email addresses, email subject, email text and the report's interval can be specified. More info available in the section "Other Notes".

= Updates =

New features has been published in the current CP Polls version 1.0.7 based on the feedback received and we would like to thank you all the people that have supported the development, provided feedback and feature requests. The plugin is currently over the 6,000 downloads/installations and a new set of updates is already being prepared, any feature requests will be welcome. Thank you!

== Installation ==

To install CP Polls, follow these steps:

1.	Download and unzip the CP Polls plugin
2.	Upload the entire cp-polls/ directory to the /wp-content/plugins/ directory
3.	Activate the CP Polls plugin through the Plugins menu in WordPress
4.	Configure the poll settings at the administration menu >> Settings >> CP Polls
5.	To insert the poll form into some content or post use the icon that will appear when editing contents

== Frequently Asked Questions ==

= Q: How can I add specific fields into the email notification? =

A: There is a tag named &lt;%INFO%&gt; that is replaced with all the information posted from the poll, however you can use also optional tags for specific fields into the poll form.

For doing that, click the desired field into the form builder and in the settings box for that field there is a read-only setting named "Field tag for the message (optional):". Copy & paste that tag into the notification message text and after the poll vote that tag will be replaced with the text entered in the poll field.

The tags have this structure (example): &lt;%fieldname1%&gt;, &lt;%fieldname2%&gt;, &lt;%fieldname3%&gt;, ...


= Q: The poll form doesn't appear. What is the solution? = 

A: The cause is in most cases a conflict with a third party plugin or with the theme. To fix that, go to the "troubleshoot area" (located below the list of polls in the settings area) change the "Script load method" from "Classic" to "Direct".

If the problem persists after that modification please contact our support service and we will give you a solution. We will appreciate any feedback to make the poll form avoid conflicts with third party plugins/themes.


= Q: I'm having problems with non-latin characters in the poll form. =

A: Use the "troubleshoot area" to change the character encoding. Try first with the UTF-8 option.


= Q:  How I can disable sending email to me everytime that somone voted ? =

A: Leave the "Destination emails" settings field empty to disable the email after each vote.


= Q: I'm getting this message: "Destination folder already exists". Solution? =

A: The free version must be deleted before installing the pro version.

If you are uploading a new version via Plugins - New - Upload and a previous version is still installed, then delete the previous version first. This is a safe step, the plugin's data and settings won't be lost during the process.

Another alternative is to overwrite the plugin files through a FTP connection. This is also a safe step.


= Q: How to edit or remove the poll title / header? =

A: Into the form builder in the administration area, click the "Form Settings" tab. That area is for editing the poll title and header text.

It can be used also for different alignment of the field labels.

= Q: Can I align the form horizontally in two or more columns? =

A: Into the poll form editor click a field and into its settings there is one field named "Add CSS Layout Keywords". Into that field you can put the name of a CSS class that will be applied to the field.

There are some pre-defined CSS classes to use align two, three or four fields into the same line. The CSS classes are named:

    column2
    column3
    column4

For example if you want to put two fields into the same horizontal line then specify for both fields the class name "column2".


= Q: How can I apply CSS styles to the poll questions? =

A: To modify the **whole styles of the poll form fields and labels**, edit the styles file "wp-content/plugins/cp-polls/css/stylepublic.css" and add these rules at the end of that file:

* **Change the styles of all the field labels:**

        #fbuilder, #fbuilder label, #fbuilder span {
        color: #00f;
        }

* **Change the poll vote button:**

        #fbuilder .pbSubmit {
        color: #00f;
        font-weight: bold;
        }
                
* **Change the "poll title" and "header description":**

        #fbuilder .fform h1 {font-size:32px;}
        #fbuilder .fform span {font-size:16px;}

**On the other hand to modify only a specific field into the poll form:**

* **Step #1:** Into the poll  form builder, click a field to edit its details, there is a setting there named "Add CSS Layout Keywords".

* **Step #2:** You can add a class name into that field, so the style specified into the CSS class will be applied to that field.

* **Step #3 (Note):** Don't add style rules directly there in the poll form builder but the name of a CSS class.

* **Step #4:** You can place the CSS class either into the CSS file of your template or into the file "wp-content/plugins/cp-polls/css/stylepublic.css" located into the CP Polls plugin's folder.

**Examples:** Add a class named "specialclass" into the setting "Add CSS Layout Keywords" and add one of these CSS rules into the mentioned file: 

* For changing the field label:

        .specialclass label {
        color: #00f;
        }


To get the modifications shown into the public poll form you may have to refresh the page twice or clear the browser cache to be sure that the old CSS styles aren't still being shown from the cache.

== Other Notes ==

**Opening the poll votes in Excel:** Go either to the "Reports" or "Votes" section. There is a button labeled "Export to CSV". CSV files can be opened in Excel, just double-click the downloaded CSV file, it will contain the selected poll votes, one per line.

**Deleting a poll vote:** Go to the "Votes" section and use the button labeled "Delete" for the poll vote you want to delete. Each row in that list is a poll vote.

**Customizing the captcha image:** The captcha image used in the poll form is 100% implemented into the plugin, this way you don't need to rely on third party services/servers. In addition to the settings for customizing the captcha design you can also replace the font files located into the folder "cp-polls/captcha/". The fonts are used as base for rendering the captcha on the poll form.

**Poll vote notification email format:** The notifications emails sent from the poll form can be either plain-text emails or HTML emails. Plain text emails are preferred in most cases since are easier to edit and pass the anti-spam filters with more probability.

**Poll Clone button:** The clone button duplicates a complete poll with its settings. The poll votes and statistics aren't duplicated.

= Custom poll vote button =  

There is a settings section info each form that allows to specify the label of the vote button. 

The class="pbSubmit" can be used to modify the button styles. 

The styles can be applied into any of the CSS files of your theme or into the CSS file "cp-polls\css\stylepublic.css". 

For further modifications the vote button is located at the end of the file "cp-public-int.inc.php".


= Customizing the automatic email reports =  

The settings for the email reports (both the global and per form reports) include the following configuration fields:

* **Enable Reports?:** Option for enabling / disabling the reports.
* **Send report every:** Indicate every how many days the reports will be sent.
* **Send after this hour (server time):** Approximate time at which the reports will be sent. This time is based on the server time. Some activity is needed on the website for sending the reports. You can setup a cron for a more exact delivery time.
* **Send email from:** The "from" email used for the reports. Avoid @aol.com and @hotmail.com "from" addresses to skip the anti-spam filters.
* **Send to email(s):** The list of emails (comma separated) that will receive the reports.
* **Email subject:** Subject of the email that will be sent with the poll reports.
* **Email format?:** Format of the email that will be sent with the poll reports. Can be HTML or Plain Text. In most cases plain text is easier to setup and has less problems with anti-spam services.
* **Email Text (CSV file will be attached):** Content of the email that will contain the poll reports. The reports will be attached in CSV format into the email.

= The poll votes database =  

The votes received via the poll form are stored into the WordPress database table "wp_cppolls_messages". You can export that data in form of automatic email reports or in CSV/Excel format from the votes list area. If needed you can also query that table directly for further processing of the poll votes.

= Importing votes =  

There is an option to import votes into the CP Polls plugin. That option is located below the votes list and is labeled "Import CSV".

The votes can be imported in a comma separated CSV file. One record per line, one field per column. Don't use a header row with the field names.

The first 3 columns into the CSV file are the time, IP address and email address, if you don't have this information then leave the first three columns empty. After those initial columns the fields (columns) must appear in the same order than in the form.

Sample format for the CSV file:

    2013-04-21 18:50:00, 192.168.1.12, john@sample.com, "john@sample.com", "sample subject", "sample message"
    2013-05-16 20:49:00, 192.168.1.24, jane.smith@sample.com, "jane.smith@sample.com", "other subject", "other message"

= From address used for the emails =  

Into the "Form Processing / Email Settings" section the first settings field is named "Send email "From" and has the following options:

* **From fixed email address indicated below - Recommended option:**  If you select "from fixed..." the customer email address will appear in the "to" address when you hit "reply", this is the recommended setting to avoid mail server restrictions.

* **From the email address indicated by the customer:** This option isn't available in this version since the poll form builder doesn't have the email field.

= The drag and drop poll form builder =  

The Poll Form Builder lets you to add/edit/remove fields into the poll form and also to specify the validation rules for your poll form (required fields). 

In other versions of the plugin the following field types are also available: Numeric field with specific validations, Date-picker, Checkboxes, Multiple Choice, Dropdown / Select, Upload file fields, Password, Phone with specific validations, static texts, test fields, email fields, textarea fields, section breaks and page breaks for multi-page poll forms.

**Other features in the poll form builder:**

 * **Dependent fields:** Use this feature for show/hide fields (any field type) based in the selection made on other fields (radiobuttons fields or also checkboxes and drop-down fields if available).

**Editing the field settings in the Poll Form Builder:**

When you click a field already added into the poll form builder area, you can edit its details and validation rules. The following properties are available:

 * **Field Label:** Label for the field in the public poll form and into the email.
 * **Field tag for the message:** In addition to the general %INFORMATION% tag, you can use this tag to show the field value into a specific tag of the email. More info at the WordPress CP Polls FAQ.
 * **Specific settings:** The settings depends of the field type.
 * **Validation rule:** The validation rules depends of the field type, the most common is "required".
 * **Predefined value:** Pre-filled value for the field, if any.
 * **Instructions for user:** This text will appear in a smaller form below the field. It's useful for giving instructions to the user.
 * **Add CSS layout keywords:** Customize the look & feel. More info at the WordPress CP Polls FAQ.


== Screenshots ==

1. The poll votes list
2. Poll reports
3. Polls list
4. Inserting a poll form into a page
5. Sample multi-question dependant poll

== Changelog ==

= 1.0.1 =
* First CP Polls stable version released.
* Visual form builder included to design the polls
* Fixed minor XSS vulnerabiliy in votes list

= 1.0.2 =
* Compatible with latest WP versions
* Bug fixed in the verification of limits per IP address
* New feature to filter by both IP and Cookies
* Fixed notices that appeared with WP_DEBUG in true
* Fixed issue with tags
* Fixed warning that appeared with PHP safe mode restrictions 

= 1.0.3 = 
* Fixed notices that appeared with WP_DEBUG in true
* Fixed issues with tags that doesnt appear correctly in the listing page.
* Update to sanitize posted data and escaping HTML

= 1.0.4 = 
* Update to avoid safe mode restriction warning.
* Improved query security
* Fixed notices that appeared with WP_DEBUG in true
* Fixed issues related to the get_site_url when WP site not in the default
* Compatible with the latest WordPress 4.2.x version

= 1.0.5 =
* Compatible with the WordPress 4.2.2 version
* Fixed XSS vulnerability

= 1.0.6 =
* Compatible with the WordPress 4.2.3 version
* Fixed issue related to the captcha image with zLib library
* Fixed bug in cache function and added ID to results.
* Fixed bug in the URLs used for Ajax calls.

= 1.0.7 =
* Compatible with the latest WordPress version
* Updated H1 and H2 tags in admin area
* Update to the submission process

= 1.0.8 =
* Tested and compatible with WordPress 4.4

== Upgrade Notice ==

= 1.0.8 =
* Tested and compatible with WordPress 4.4

Important note: If you are using the Professional version don't update via the WP dashboard but using your personal update link. Contact us if you need further information: http://wordpress.dwbooster.com/support