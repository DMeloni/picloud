// variables
var dropArea = document.getElementById('dropArea'); // drop area zone JS object
var count = document.getElementById('count'); // text zone to display nb files done/remaining
var progress = document.getElementById('progress'); // text zone where informations about uploaded files are displayed
var list = []; // file list
var nbDone = 0; // initialisation of nb files already uploaded during the process.
var nb=null;
var nbUploaded = null;
var oldColor = null;
var uploadError = false;

// main initialization
(function(){

    // init handlers
    function initHandlers() {
        dropArea.addEventListener('drop', handleDrop, false);
        dropArea.addEventListener('dragover', handleDragOver, false);
    }

    // drag over
    function handleDragOver(event) {
        event.stopPropagation();
        event.preventDefault();
        oldColor = dropArea.style.color;
        dropArea.style.color='red';
        //dropArea.className = 'hover';
    }

    // drag drop
    function handleDrop(event) {
        dropArea.style.color=oldColor;
        event.stopPropagation();
        event.preventDefault();

        processFiles(event.dataTransfer.files);
        
        
    }

    // process bunch of files
    function processFiles(filelist) {
        if (!filelist || !filelist.length || list.length) return;

        //result.textContent = '';

        for (var i = 0; i < filelist.length && i < 500; i++) { // limit is 500 files (only for not having an infinite loop)
            nbUploaded=filelist.length;
            list.push(filelist[i]);
        }
        uploadNext();
    }

    // upload file
    function uploadFile(file, status) {

        // prepare XMLHttpRequest
        var xhr = new XMLHttpRequest();
        xhr.open('POST', rootDirectory + 'upload.php?pathname='+window.location.pathname);
        console.log(rootDirectory + 'upload.php?pathname='+window.location.pathname);
        xhr.onload = function() {
            uploadNext();
            nbUploaded--;
            if(nbUploaded == 0 && uploadError==false){
                window.location.href = unescape(window.location.pathname);
            }
        };
        var totalTmp = 0;
        if ( xhr.upload ) {
            xhr.upload.onprogress = function(e) {
                var done = e.position || e.loaded, total = e.totalSize || e.total;
                totalTmp = e.total;
                var pourcentage = Math.floor(done/total*1000)/10;
                var progressMessage = "File : " + file['name'] + " ("+file['type']+") progress : " + pourcentage + "%" + " ("+done+"/"+total+" octets)";
                var fileDiv = document.getElementById('file_'+nbDone+'');
                fileDiv.textContent = progressMessage;
            };
        }
        
        xhr.onreadystatechange = function() {
            if(xhr.readyState == 4){
                var progressMessage = "File : " + file['name'] + " ("+file['type']+") progress : 100%" + " (" + file['size'] + " octets)";
                var fileDiv = document.getElementById('file_'+nbDone+'');
                fileDiv.textContent = progressMessage;
            }
        };

        xhr.onerror = function() {
            var progressMessage = "File : " + file['name'] + " ("+file['type']+") upload error";
            var fileDiv = document.getElementById('file_'+nbDone+'');
            fileDiv.textContent = progressMessage;
            uploadNext();
        };

        // prepare and send FormData
        var formData = new FormData();  
        formData.append('myfile', file); 
        xhr.send(formData);
    }

    // upload next file
    function uploadNext() {
        if (list.length) {
            nb = list.length - 1;
            
            nbDone +=1;
            
            var strTemp = '<div id="file_'+nbDone+'"></div>';
            progress.innerHTML += strTemp;
            
            var nextFile = list.shift();
            var sizeMax = 8388608;
            if (nextFile.size >= sizeMax) { // 20Mb = generally the max file size on PHP hosts
                var progressMessage = "Fichier : " + nextFile['name'] + " ("+nextFile['type']+") File Too big (" + nextFile['size'] + " > "+sizeMax+")";
                var fileDiv = document.getElementById('file_'+nbDone+'');
                fileDiv.textContent = progressMessage;
                uploadError = true;
                uploadNext();
            } else {
                uploadFile(nextFile, status);
            }
            
        }

    }

    initHandlers();
})();