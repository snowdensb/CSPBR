;(function($) {
    window.WyoUI = (function()
    {
        var _private =
        {
            lat: 0,
            long: 0,
            webRootPath: '',
            errorList: [],
            apiPath: function() {
                return this.webRootPath;
            },
            request: function(id, method, url, data, dataItem, filesPresent)
            {
                _private.log('in request, Fetching [' + url +']');
                var contentTypeValue = 'application/x-www-form-urlencoded; charset=UTF-8';
                var processDataValue = true;

                // if sending files, set the following
                if (filesPresent) {
                    _private.log('we have files to send, setting contentType and processData to false');
                    // readmore : http://api.jquery.com/jquery.ajax/
                    contentTypeValue = false;
                    processDataValue = false;
                }

                $.ajax({
                        dataType: "json",
                        type: method,
                        url: url,
                        data: data,
                        cache: false,
                        contentType: contentTypeValue,
                        processData: processDataValue,
                        success: function(data){
                            console.log("back from ajax call, in success function");
                            if (data.results.status == 1 && id !== '') {
                                $(id).html(data.results.newHtml);
                            }  else if (typeof data.results.init != 'undefined' ) {
                                // Custom javascript for this request wants to handle
                                // both success and failure. Let her.
                                console.log('custom function: ' + data.results.init);
                                var initFunction = new Function('p1', data.results.init);
                                initFunction(data);
                            } else {
                                // We need something more elegant than this but, for now, 
                                // don't be silent when we round trip with unexpected return values.
                                var msg = 'Request succeeded. ';
                                if (data.results.status == 0) {
                                    msg = 'Request failed. ';
                                } 
                                if (data.results.message) {
                                    msg = msg + data.results.message;
                                }
                                alert(msg);
                            }
                        },
                    error: function(req, textStatus, errorThrown){
                        console.log("back from ajax call, in failure function");
                        alert('Unable to complete request.  Please try again.');
                        console.log('Request failed: [' + textStatus + ']');
                    }
                });
            },
            log: function(message) {
            if (window.console)
                console.log(message);
            }
        };
        return {
            get: function(path,elementId)
            {
                _private.request('#'+elementId,'GET', _private.apiPath() + path, null, null, false);
                return false;
            },
            getData: function(path,elementId,data)
            {
                var encodedData = $.param( data );
                _private.request('#'+elementId,'GET', _private.apiPath(), encodedData, null, false);
                return false;
            },
            // Inquiring minds often need to be reminded of why 
            // post uses .serialize() and postFiles uses FormData.
            // Succinct answer here.
            // http://stackoverflow.com/questions/33469684/formdata-vs-serialize-what-is-the-difference
            // At this point in IE history, we probably should just use FormData both places.
            post: function(path, formId, elementId)
            {
                var formData = $('#'+formId).serialize();
                var localElementId = '';
                if (elementId !== '') {
                    localElementId = '#'+elementId;
                }
                _private.request(localElementId,'POST', _private.apiPath() + path, formData, null,false);
                return false;
            },
            postFiles: function(path, formId)
            {
                _private.log("in postFiles, path = " + path + ", formId = " + formId);
                var formElement = $('#'+formId)[0];
                // Serialize the fields and files from the form
                var formData = new FormData(formElement);
                _private.request('','POST', _private.apiPath() + path, formData, null, true);
                _private.log("postFiles request made");
                return false;
            },
            confirm: function(msg) {
                var r = confirm(msg);
                return r;
            },
            hideMessage: function() {
                if ($('.message').length > 0) {
                    setTimeout(function(){$('.message').slideToggle();},3000);
                }
            },
            showPreview: function(imageInput) {
                if (imageInput.files && imageInput.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function (e) {
                        $('#'+imageInput.id+'-preview').attr('src', e.target.result).slideDown();
                    }

                    reader.readAsDataURL(imageInput.files[0]);
                }
            },
            getWebRootPath: function() {
                return _private.webRootPath;
            },
            init: function(path)
            {
                _private.webRootPath = path;
            }
        };
    })();
})(jQuery);


$(document).ready(function() {
    WyoUI.init(appConfig.appPath);
    //console.log("js-wyolution.js after init");
});


