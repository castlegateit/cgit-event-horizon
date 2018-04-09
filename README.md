# Castlegate IT Log Truncation (Event Horizon) #

This plugin provides basic functionality to assist with GDPR compliance by truncating .csv log files based on date.

It can be used on static sites via include or installed as a WordPress plugin.

## Class and functions ##

Basic use assumes a 6-month expiration date on logs and comma separated values, accepting the file path of a CSV file
to truncate as an argument.

~~~ php
$plugin = new \Cgit\EventHorizon('\example.csv');
$plugin->ensureCompliance();
~~~

You can configure the horizon beyond which logs will be deleted:
~~~ php
// Set logs to be truncated if an entry is older than 100 days.
$plugin = new \Cgit\EventHorizon('\example.csv', 864000);
// Perform truncation process.
$plugin->ensureCompliance();
~~~

This plugin assumes that the time format 'Y-m-d H:i' is in use to detect timestamps in log files. If necessary you can 
alter it.
~~~ php
$plugin = new \Cgit\EventHorizon('\example.csv');
// Set timestamp format.
$plugin->setTimeFormat('d-m-Y H:i');
~~~

The plugin will detect the first timestamp in a given row and use it to "date" the row, but you can specify a column manually if you wish, or if there are multiple timetimes such as for an event booking:
~~~ php
$plugin->setManualTimestampColumn(1);
~~~

The delimiter used to split up rows defaults to a comma, but can also be changed.
~~~ php
// Set Delimiter.
$plugin->file->setDelimiter('#');
~~~

#### Example of handling multiple log files #### 

~~~ php
$logfiles = [
    '/home/srv/www/example-site/htdocs/log.csv',
    '/home/srv/www/example-site/htdocs/log2.csv',
    '/home/srv/www/example-site-two/htdocs/log.csv',
    '/home/srv/www/example-site/htdocs/applications.csv',
];

foreach($logfiles as $logfile) {
    $EventHorizon = new \Cgit\EventHorizon('logfile');
    $EventHorizon->ensureCompliance();
}
~~~