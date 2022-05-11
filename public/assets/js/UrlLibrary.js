const UrlLibrary = (function() {
    return {
        queryStringToObject: function(queryString) {
            return Object.fromEntries(new URLSearchParams(queryString));
        },
        objectToString: function(oQueryParams) {
            return new URLSearchParams(oQueryParams).toString();
        }
    }
})();