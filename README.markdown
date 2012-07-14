The Colombo Bus Route Finder (Version 2.0)
==========================================

Created over one fine weekend in July by [Janith Leanage](http://janithl.blogspot.com)  
Contains code written by [Thimal Jayasooriya](https://github.com/thimal)

API and Client for Bus Route Finder

License
-------

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU Affero General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Affero General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.


Changes
-------

* System uses distance instead of number of halts to measure distance now

* The ability to use coordinates as inputs

* An API that returns JSON! :D

* Cleaner code

* Refactoring
	
Instructions
------------

* Create a database and run `bus.sql`

* Upload files into server, create a `cache` folder on the same directory as 
index.php for caching.

* Edit database details and other settings in `config.inc.php` in the `inc`
folder.


Using the API
-------------

You have to hit the API with two query strings, `from` and `to`. These can either
be strings (we will try to match them with a list of locations in the database)
or latitude,longitude pairs seperated by commas.

	http://host.com/?from=56.3,4.3&to=8.3,4.2
	
The API will return a JSON response string.

* `['title']` contains the title (e.g. Buses from Kottawa to Town Hall)

* `['links']` is an array that contains all the suggested routes

*  `['links'][<element number>]['nobuses']` gives you the number of buses, and 
`['links'][<element number>]['totaldist']` gives you the total distance in metres.

* `['links'][<element number>]['inst']` is an array with step by step instructions
on how to take the bus.


More Information
----------------

Do not hesitate to contact me on Twitter: `@chav_` :)
