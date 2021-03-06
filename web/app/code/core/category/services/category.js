'use strict';

angular.module('aiselApp')
    .service('categoryService', ['$http','$routeParams','API_URL',function ($http, $routeParams, API_URL) {
        return {

            getCategories: function($scope) {
                var url = API_URL+'/page/category/list.json?limit='+$scope.pageLimit+'&current='+$scope.paginationPage;
                console.log(url);
                return $http.get(url);
            },

            getCategory: function(categoryId) {
                var url = API_URL+'/page/category/view/'+categoryId+'.json';
                console.log(url);
                return $http.get(url);
            }
        };

    }]);
