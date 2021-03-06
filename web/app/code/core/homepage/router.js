'use strict';

/**
 * @ngdoc overview
 * @name aiselApp
 * @description
 * # aiselApp
 *
 * Router for Homepage
 */

aiselApp.config(function ($provide, $routeProvider) {

    $routeProvider

        // Homepage
        .when('/', {
            templateUrl: 'app/views/core/homepage/homepage.html',
            controller: 'HomepageCtrl'
        })

});
