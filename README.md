psElkStackLog
=========

Send shop information to log server, eg. elasticsearch, solr, graylog, ...
Free module for OXID eshop 4.7, 4.8, 4.9 and 4.10.

Features

	- saves search information to log queue
	- saves order / orderarticle information to log queue
	- uses conrjob to tranfer queue to log server (curl)

Installation

	1. copy content from copy_this folder into your shop root
	2. activate module psElkStackLog in shop admin

Tip: Use the [OXID module connector](https://github.com/OXIDprojects/OXID-Module-Connector) to install this module.


Screenshot

![psElkStackLog](https://raw.github.com/proudcommerce/psElkStackLog/master/pselkstackLog_graylog.png)

(graylog Dashboard)


Changelog

	2017-01-23	1.2.0	add log queue
	2016-07-12	1.1.0	add search logging
	2016-04-01  1.0.0   module release
	
License

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
    

Copyright

	Proud Sourcing GmbH 2017
	www.proudcommerce.com / www.proudsourcing.de