class UploadAdapter {
    constructor( loader ) {
        // The file loader instance to use during the upload.
        this.loader = loader;
    }

    // Starts the upload process.
    upload() {
        return this.loader.file
            .then( file => new Promise( ( resolve, reject ) => {
                this._initRequest();
                this._initListeners( resolve, reject, file );
                this._sendRequest( file );
            } ) );
    }

    // Aborts the upload process.
    abort() {
        if ( this.xhr ) {
            this.xhr.abort();
        }
    }

    // Initializes the XMLHttpRequest object using the URL passed to the constructor.
    _initRequest() {
        const xhr = this.xhr = new XMLHttpRequest();
        xhr.withCredentials = true;

        // Note that your request may look different. It is up to you and your editor
        // integration to choose the right communication channel. This example uses
        // a POST request with JSON as a data structure but your configuration
        // could be different.
        xhr.open( 'POST', '/file/upload', true );
        xhr.responseType = 'json';
    }

    // Initializes XMLHttpRequest listeners.
    _initListeners( resolve, reject, file ) {
        const xhr = this.xhr;
        const loader = this.loader;
        const genericErrorText = `Couldn't upload file: ${ file.name }.`;

        xhr.addEventListener( 'error', () => reject( genericErrorText ) );
        xhr.addEventListener( 'abort', () => reject() );
        xhr.addEventListener( 'load', () => {
            const response = xhr.response;

            // This example assumes the XHR server's "response" object will come with
            // an "error" which has its own "message" that can be passed to reject()
            // in the upload promise.
            //
            // Your integration may handle upload errors in a different way so make sure
            // it is done properly. The reject() function must be called when the upload fails.
            if ( !response || response.error ) {
                return reject( response && response.error ? response.error.message : genericErrorText );
            }

            // If the upload is successful, resolve the upload promise with an object containing
            // at least the "default" URL, pointing to the image on the server.
            // This URL will be used to display the image in the content. Learn more in the
            // UploadAdapter#upload documentation.
            resolve( {
                default: response.url
            } );
        } );

        // Upload progress when it is supported. The file loader has the #uploadTotal and #uploaded
        // properties which are used e.g. to display the upload progress bar in the editor
        // user interface.
        if ( xhr.upload ) {
            xhr.upload.addEventListener( 'progress', evt => {
                if ( evt.lengthComputable ) {
                    loader.uploadTotal = evt.total;
                    loader.uploaded = evt.loaded;
                }
            } );
        }
    }

    // Prepares the data and sends the request.
    _sendRequest( file ) {
        // Prepare the form data.
        const data = new FormData();

        data.append( 'upload', file );

        // Important note: This is the right place to implement security mechanisms
        // like authentication and CSRF protection. For instance, you can use
        // XMLHttpRequest.setRequestHeader() to set the request headers containing
        // the CSRF token generated earlier by your application.

        // Send the request.
        this.xhr.send( data );
    }
}

// ...

function GetUploadAdapter( editor ) {
    editor.plugins.get( 'FileRepository' ).createUploadAdapter = ( loader ) => {
        console.log(loader);
        return new UploadAdapter( loader );
    };
}

function notepad(options = Object){
    title = options.title ?? "";
    body = options.body ?? "";

    var element = `<div class="editor flex">
    <div class="col-md-12 titlebar">
        <div class="row">
            <span><i class="las la-scroll"></i> Editor</span>
            <span class="ml-auto">
                <button class="btn" id="notepad_save"><i class="las la-save"></i></button>
                <button class="btn" id="notepad_close"><i class="las la-window-close"></i></button>
            </span>
        </div>
    </div>
    <div class="row body">
        <form>
            <lable>Title: </lable>
            <input class="form-control" type="text" name="title" id="title" placeholder="Title" maxlength="70" value="${title}">
            <lable>body: </lable>
            <textarea class="form-control" name="body" id="body" cols="30" rows="10" placeholder="Body">${body}</textarea>
        </form>
    </div>
    </div>`;

    element = $(element)
    $('body').append(element);
    
    ClassicEditor.create( document.querySelector( '#body' ),{
        extraPlugins: [ GetUploadAdapter ],
    }).then( editor => {
                window.editor = editor;
        } )
        .catch( error => {
                console.error( error );
        } );

    $('#notepad_close').on('click', function(){
        if(options.onClose)
            options.onClose();
        element.remove();
    });

    $('#notepad_save').on('click', function(){
        if(options.onSave){
            $(this).prop('disabled', true);
            title = $('#title').val();
            body = editor.getData();
            options.onSave({title:title, body:body});
        }
    });
}

function viewer(options = Object){
    title = options.title ?? "";
    body = options.body ?? "";
    var element = `<div class="editor flex">
    <div class="col-md-12 titlebar">
        <div class="row">
            <span><i class="lab la-readme"></i> Viewer</span>
            <span class="ml-auto">
                <button class="btn" id="viewer_close"><i class="las la-window-close"></i></button>
            </span>
        </div>
    </div>
    <div class="row h-100" style="overflow-y: scroll; margin: 0;">
        <div class="col-12">
            <h1>${title}</h1>
            <div>
                ${body}
            </div>        
        </div>
    </div>
    </div>`;

    element = $(element)
    $('body').append(element);

    $('#viewer_close').on('click', function(){
        element.remove();
    });
}

function notepad_close(){
    $('.editor').remove();
}