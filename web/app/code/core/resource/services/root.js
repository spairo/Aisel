'use strict';

angular.module('aiselApp')
    .service('rootService', ['$http','$routeParams','$rootScope','API_URL',function ($http, $routeParams, $rootScope, API_URL) {
        return {

            init: function() {
                // Set default values
                var meta = false;
                var disqus = false;
                $rootScope.$on('$routeChangeStart', function (event, to, from) {
                    $rootScope.pageTitle = meta.defaultMetaTitle;
                    $rootScope.metaDescription = meta.defaultMetaDescription;
                    $rootScope.metaKeywords = meta.defaultMetaKeywords;
                });
                this.getApplicationConfig().success(
                    function(data, status) {
                        // Meta
                        meta = JSON.parse(data.config_meta);
                        $rootScope.pageTitle = meta.defaultMetaTitle;
                        $rootScope.metaDescription = meta.defaultMetaDescription;
                        $rootScope.metaKeywords = meta.defaultMetaKeywords;

                        // Disqus
                        disqus = JSON.parse(data.config_disqus);
                        $rootScope.disqusShortname = disqus.shortname;
                        $rootScope.disqusStatus = disqus.status;
                    }
                );

                // Load menu, categories and so on ...
                this.getMenu().success(
                    function(data, status) {
                        $rootScope.topMenu = data;
                    }
                );
                this.getCategoryTree().success(
                    function(data, status) {
                        $rootScope.categoryTree = data;
                    }
                );
                this.getUserInformation().success(
                    function(data, status) {
                        $rootScope.isAuthenticated = false;
                        if (data.username) {
                            $rootScope.isAuthenticated = true;
                            $rootScope.user = data;
                        }
                    }
                );


            },

            getApplicationConfig: function() {
                var url = API_URL+'/config/settings.json';
                console.log(url);
                return $http.get(url);
            },
            getCategoryTree: function() {
                var url = API_URL+'/page/category/tree.json';
//                console.log(url);
                return $http.get(url);
            },
            getMenu: function() {
                var url = API_URL+'/navigation/menu.json';
//                console.log(url);
                return $http.get(url);
            },
            getUserInformation: function() {
                var url = API_URL+'/user/information.json';
//                console.log(url);
                return $http.get(url);
            }

        };

    }]);
