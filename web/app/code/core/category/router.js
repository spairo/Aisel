'use strict';

/**
 * @ngdoc overview
 * @name aiselApp
 * @description
 * # aiselApp
 *
 * Categorisation router
 */

aiselApp.config(function ($provide, $routeProvider, $locationProvider, $httpProvider) {

    $routeProvider

        // Categories
        .when('/categories/', {
            templateUrl: 'app/views/core/category/category.html',
            controller: 'CategoryCtrl'
        })
        .when('/category/:categoryId/', {
            templateUrl: 'app/views/core/category/category-detail.html',
            controller: 'CategoryDetailCtrl'
        })

});
