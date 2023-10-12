# LaravelNeverCRUD
Never write CRUD code in projects using Laravel ever again... or something as close as possible to that...

## Scope
This is a helper package that is designed to automate writing CRUD for projects using Laravel. 
CRUD is repetitive enough that 95% of the time you are doing the same things again and again:
* Get input
* Validate input
* Do some work depending on input, which will be one of 
  * Create 
  * Update
  * Delete
  * Duplicate
  * Change dynamic properties
* Return a result (some times the new object, an ID, a status or an error/exception)
NeverCRUD is designed to automate all these tasks to the point that you only have to write two PHP arrays containing validation meta-data, one for creation input validation and another for update input validation, as these are usually different. IF they are the same for your case, lucky you, you write only one.

## Is it rigid? Will it tie my hands and frustrate me?
No. You can customize to your heart’s desire. Every aspect of CRUD can be overridden. You don't even have to use it on all aspects of your project too...

## Is it magic? 
No. 

## Does it have limitations? 
Of course. If your particular project has very complicated requirements you may end up doing more work than actually implementing CRUD from scratch. This is no magic bullet, you have to think and decide.

## Is there a gotcha?
~~Yes ... and~~ no.

## Can you be more specific?
Well... If you use the MVC version of CRUDController (CRUDControllerMVC) everything is as you know it. If not ... we may be changing the whole MVC paradigm of Laravel and extending it to something a bit more complex. 
This whole thing might sound like a big deal but it isn't. At least as soon as you understand why ~~this package~~ the non MVC version was created and what it is supposed to help you with.
~~We have a version in the works that doesn't do that but it seems a bit lame at the moment. It will be included in the package as soon as we make it a little less lame than it is.~~
The MVC version is included with 1.1.x and you can use it at will.

## Aren't you meddling with things that you are not supposed to with the non-MVC version?
Probably yes. 

## Do you honestly think that you know better than the people that made and maintain Laravel?
No. We certainly don't. And this package is not necessarily useful to you. If you are just making a web site with Laravel, there is a substantial possibility that this is something you don't quite need. If on the other hand you are making a system that contains business logic and provides an API (and optionally a web site too) then this is definitely something that might benefit you. In the first case use the MVC version to speed up prototyping and development. In the second case you are encouraged to take a deeper look and use the non MVC version.

## Ok, I am completely lost here, can you PLEASE stop fooling around and explain EXACTLY what this does and how ?
Sure.

## Well ???
Ok. Laravel uses MVC. Models to access and manipulate data. Views for displaying them inside premade templates and Controllers to fill the Views with the data. Classic stuff. CRUDControllerMVC follows this too. 
NeverCRUD (the CRUDController/CRUDHandler/ModelDataService combination) changes this. We introduce the concept of handlers which actually do the work and leave to the controllers the responsibility of sanitizing input. ***WARNING*** Security is your responsibility. Sanitizing and validating input is only ***PART*** of security and is handled by the NeverCRUD derived controllers automatically. ***THAT DOESN'T MEAN YOU ARE SECURE!!!!*** Everything else security wise is ***YOUR*** responsibility. 
Handlers (derived from the NeverCRUD handler) house the business logic. Handlers don't manipulate data directly. *No cheating please*. NeverCRUD introduces the concept of DataServices. One included data service is ModelDataService which is using models to do its thing. In planning we have a CachedModelDataService that includes the use of cache. Another under development is the APIDataService. What that does is left for you to imagine. 

## Why do all that? Handlers? DataServices? What is the benefit?
Imagine the following scenario:
  * you are building a system for an insurance company.
  * you need to present your data in several interfaces:
    * one for the company employees
    * one for the company's agents
    * one for the web for all the people of this world
    * which has an area reserved for insurance holders that need to see their policies and payments
    * one for the policy holder's mobile phones via a native application.
  * each of these interfaces needs a different set of functionalities and security concerns.
  * this whole system needs to be white labeled because we are a platform company too...
  * this system needs to acquire data from external sources like address validation, credit score data and risk assessment services. 
Not everything is coming from or going to your database. Hence the data aservices. You need to do many things which may or may not overlap between each of your interfaces, and you definitelly can't just stick everything in one controller. Hence the need for Handlers. And in the very end this is a huge system that needs robustness, scaling etc and it is diverse enough to warrant microservices. Writing crud for each and every one of them models in each and every microservice is very tedious and error prone. Hence the need for automation of CRUD.

## Seems the architectural aspect is quite important too...
Depending on what you are doing it is very important. I have been studying DDD and other architectural stuff for some years now and this might end up a new paradigm or a variation of DDD. For the moment let's pretend that this is just a CRUD alleviation package :-) 

## Versioning
Always use the latest release of the major version you have started with. We don't expect breaking changes in the foreseeable future so all 1.x.x versions should be drop in replacements for previous releases. Breaking changes will be denoted by the increase of the major version number. So for this project:
* 1.x.x <- major version increments only for breaking changes in the project
* x.1.x <- subversion that denotes the features present. Will increment as features are added. 
* x.x.1 <- minor version. Will increment for bugfixes, types, non feature additions or code quality refactorings. 
For example the change that added this paragraph to the readme.md file will increment the release from v1.0.3 to v1.0.4 . When (if?) dynamic properties are added version will go from v1.0.x to 1.1.x . Updating to that version will __not__ break your code. Promise. 

## Examples? Tutorials?
~~Too lazy. If there is enough interest will do it though.~~
I got my lazy bum to write an example that you can find here:

[LaravelNeverCRUD-example](https://github.com/IoannisLoukeris/LaravelNeverCRUD-example)

