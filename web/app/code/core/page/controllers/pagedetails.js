'use strict';

angular.module('aiselApp')
  .controller('PageDetailCtrl', function ($scope, $routeParams, pageService, $rootScope, rootService) {

        var pageURL = $routeParams.pageId;
        var handleSuccess = function(data, status) {
            $scope.pageDetails = data;
            $rootScope.pageTitle = $scope.pageDetails.page.title;

            // Disqus comments
            window.disqus_shortname = $rootScope.disqusShortname;
            $scope.showComments = $rootScope.disqusStatus && $scope.pageDetails.page.comment_status;
        };
        pageService.getPageByURL(pageURL).success(handleSuccess);

  });
