<?php use Src\Components\Constants; ?>
<script>
    class MediaLibrary 
    {
        root = <?php echo json_encode($session->getAuth() ? Constants::getUserStorageRoot($session->getAuth()) : '') ?>;
        path = <?php echo json_encode(url()) ?>;
        filepath = '';
        maxSize = 2;
        pageLimit = 30;
        fileTypes = ['jpeg', 'jpg', 'png', 'gif'];
        success = function(path) {};
        typesList = {
            jpeg: {
                type: "icofont-file-jpg", 
                color: "text-info"
            },
            jpg: {
                type: "icofont-file-jpg", 
                color: "text-info"
            },
            png: {
                type: "icofont-file-png", 
                color: "text-danger"
            },
            gif: {
                type: "icofont-file-gif", 
                color: "text-primary"
            },
            pdf: {
                type: "icofont-file-pdf", 
                color: "text-danger"
            },
            doc: {
                type: "icofont-file-document", 
                color: "text-primary"
            },
            docx: {
                type: "icofont-file-document", 
                color: "text-primary"
            },
            ppt: {
                type: "icofont-file-presentation", 
                color: "text-danger"
            },
            pptx: {
                type: "icofont-file-presentation", 
                color: "text-danger"
            },
            csv: {
                type: "icofont-file-excel", 
                color: "text-success"
            },
            xls: {
                type: "icofont-file-excel", 
                color: "text-success"
            },
            xlsx: {
                type: "icofont-file-excel", 
                color: "text-success"
            }
        };

        constructor() 
        {
            this.app = new App();

            this.modal = $('#media-library-modal');
            this.upload = document.getElementById('ml-upload');
            this.uploadArea = document.getElementById('ml-upload-area');
            this.form = document.getElementById('ml-images-list');
            this.list = document.getElementById('ml-list-group');
            this.maxSizeText = document.getElementById('ml-maxsize');
            this.choosenFile = document.getElementById('ml-choosen-file').value;
            this.chooseButton = document.getElementById('ml-choose');
            this.cancelButton = document.getElementById('ml-cancel');
            this.progressPercentage = document.getElementById('ml-progress');
            this.progressBar = document.getElementById('ml-progress-bar');
            this.uploadTabButton = document.querySelector("a[href='#ml-tab-1']");
            this.uploadTab = document.getElementById('ml-tab-1');
            this.imageCaptureTabButton = document.querySelector("a[href='#ml-tab-2']");
            this.imageCaptureTab = document.getElementById('ml-tab-2');
            this.pagination = document.getElementById('ml-pagination');
            this.MLTabButton = document.querySelector("a[href='#ml-tab-3']");
            this.MLTab = document.getElementById('ml-tab-3');
            this.MLVideo = document.getElementById('ml-video');
            this.MLCanvas = document.getElementById('ml-canvas');
            this.MLSnap = document.getElementById('ml-snap');
            this.MLSaveSnap = document.getElementById('ml-save-snap');
            this.MLCameraSelect = document.getElementById('ml-camera-select');
            this.MLCleanSearch = document.getElementById('ml-clean-search');
            
            this.addEvents();
        }

        setRoot(value) 
        {
            this.root = value;
            return this;
        }

        setMaxSize(value) 
        {
            this.maxSize = value;
            return this;
        }

        setPageLimit(value) 
        {
            this.pageLimit = value;
            return this;
        }

        setFileTypes(value) 
        {
            this.fileTypes = value;
            return this;
        }

        setSuccess(value = function(path) {}) 
        {
            this.success = value;
            return this;
        }

        open()
        {
            this.choosenFile = null;
            this.setButtonStatus();
            this.loadFiles();
            this.maxSizeText.innerHTML = <?php echo json_encode(sprintf(_('Tamanho máximo permitido: %sMB'), '{max_size}')) ?>
                .replace('{max_size}', `${this.maxSize}`);

            this.modal.modal('show');
        }

        addEvents() 
        {
            this.initCamera();

            this.uploadArea.addEventListener('mouseenter', function() {
                document.getElementById('ml-area').classList.add('bg-info');
                document.getElementById('ml-area').classList.add('text-white');
            });

            this.uploadArea.addEventListener('mouseleave', function() {
                document.getElementById('ml-area').classList.remove('bg-info');
                document.getElementById('ml-area').classList.remove('text-white');
            });

            this.upload.addEventListener('dragenter', function() {
                document.getElementById('ml-area').classList.add('bg-info');
            });

            this.upload.addEventListener('dragleave', function() {
                document.getElementById('ml-area').classList.remove('bg-info');
            });

            this.upload.addEventListener('drop', function() {
                document.getElementById('ml-area').classList.remove('bg-info');
            });

            this.upload.addEventListener('change', (event) => {
                this.addFile(event);
            });

            this.form.onsubmit = event => {
                event.preventDefault();
                var term = this.form.search.value;
                this.choosenFile = null;
                this.setButtonStatus();
                this.loadFiles(term);
            };

            this.MLCleanSearch.onclick = () => {
                this.choosenFile = null;
                this.form.search.value = null;
                this.setButtonStatus();
                this.loadFiles();
            };
            
            if(!this.choosenFile) {
                this.chooseButton.disabled = true;
            }

            this.chooseButton.onclick = () => {
                if(this.chooseButton.disabled == false) {
                    if(this.checkFileExtension(this.choosenFile)) {
                        this.filepath = this.returnFilePath(this.choosenFile);
                        this.success(this.filepath);
                        this.modal.modal('toggle');
                    } else {
                        var str = this.fileTypes.join(", ");
                        this.app.showMessage(
                            <?php echo json_encode(
                                sprintf(
                                    _('A extensão do arquivo que você selecionou não é permitida aqui! Extensões permitidas: %s'), 
                                    '{extensions}'
                                )
                            ) ?>.replace('{extensions}', str), 
                            'error'
                        );
                    }
                }
            };

            this.cancelButton.onclick = () => {
                this.modal.modal('toggle');
            };
        }

        async initCamera() 
        {
            const object = this;
            try {
                const devices = await navigator.mediaDevices.enumerateDevices();
                const videoDevices = devices.filter(device => device.kind === 'videoinput');
                const options = videoDevices.map(videoDevice => {
                    let optionElem = document.createElement('option');
                    optionElem.setAttribute('value', videoDevice.deviceId);
                    optionElem.innerHTML = videoDevice.label;
                    return optionElem;
                });

                if(options) {
                    options.forEach(function (elem, i) {
                        object.MLCameraSelect.appendChild(elem);
                    });

                    const stream = await navigator.mediaDevices.getUserMedia({ 
                        audio: false, 
                        video: {
                            deviceId: options[0].value
                        }
                    });
                    window.stream = stream;
                    object.MLVideo.srcObject = stream;

                    await new Promise(resolve => object.MLVideo.onloadedmetadata = resolve);

                    object.MLCanvas.width = object.MLVideo.videoWidth;
                    object.MLCanvas.height = object.MLVideo.videoHeight;

                    var context = object.MLCanvas.getContext('2d');
                    object.MLSnap.addEventListener('click', function () {
                        context.drawImage(object.MLVideo, 0, 0, object.MLVideo.videoWidth, object.MLVideo.videoHeight);
                        object.MLSaveSnap.removeAttribute('disabled');
                    });
    
                    object.MLSaveSnap.addEventListener('click', function () {
                        object.MLSaveSnap.setAttribute('disabled', true);
                        var dataURL = object.MLCanvas.toDataURL();
                        var byteString = atob(dataURL.split(',')[1]);
                        var mimeString = dataURL.split(',')[0].split(':')[1].split(';')[0];
    
                        var ab = new ArrayBuffer(byteString.length);
                        var ia = new Uint8Array(ab);
                        for (var i = 0; i < byteString.length; i++) {
                            ia[i] = byteString.charCodeAt(i);
                        }
    
                        const file = new Blob([ab], {
                            type: mimeString
                        });
                        file.name = 'image-capture.png';
                        file.full_path = 'image-capture.png';
    
                        var formData = new FormData();
                        formData.append('file', file);
                        formData.append('root', object.root);
    
                        $.ajax({
                            url: <?php echo json_encode($router->route('mediaLibrary.add')) ?>,
                            type: "post",
                            data: formData,
                            dataType: 'json',
                            success: function (response) {
                                if(response.message) {
                                    object.app.showMessage(response.message[1], response.message[0]);
                                }
    
                                object.addFileToList(response.filename);
                                object.chooseFile(response.filename);
                                object.openMediaLibraryTab();
                            },
                            error: function (response) {
                                if(response.responseJSON && response.responseJSON.message) {
                                    object.app.showMessage(response.responseJSON.message[1], "error");
                                }
                            },
                            complete: function () {
                                object.MLSaveSnap.removeAttribute('disabled');
                            },
                            contentType : false,
                            processData : false
                        });
                    });
                }

                object.MLCameraSelect.onchange = () => {
                    object.setCameraByDeviceId(object.MLCameraSelect.value);
                }
            } catch(e) {
                console.log(e.toString());
            }
        }

        async setCameraByDeviceId(deviceId) 
        {
            const object = this;

            try {
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: {
                        deviceId: deviceId
                    }
                });
                window.stream = stream;
                object.MLVideo.srcObject = stream;
                await new Promise(resolve => object.MLVideo.onloadedmetadata = resolve);

                object.MLCanvas.width = object.MLVideo.videoWidth;
                object.MLCanvas.height = object.MLVideo.videoHeight;
                object.MLSaveSnap.setAttribute('disabled', true);
            } catch(e) {
                console.log(e.toString());
            }
        }

        openMediaLibraryTab() 
        {
            this.imageCaptureTabButton.classList.remove("active", "show");
            this.imageCaptureTab.classList.remove("active", "show");

            this.uploadTabButton.classList.remove("active", "show");
            this.uploadTab.classList.remove("active", "show");

            this.MLTabButton.classList.add("active", "show");
            this.MLTab.classList.add("active", "show");
        }

        addFile(event) 
        {
            var object = this;

            const file = event.target.files[0];
            const filename = file.name;

            if(object.checkFileExtension(filename)) {
                if(file.size > object.maxSize * 1024 * 1024) {
                    object.app.showMessage(
                        <?php echo json_encode(sprintf(_('O arquivo que você tentou enviar é maior do que %sMB!'), '{max_size}')) ?>
                            .replace('{max_size}', `${object.maxSize}`), 
                        "error"
                    );
                } else {
                    var reader = new FileReader();

                    reader.readAsDataURL(file);
                    reader.onload = function(event) {
                        var base64data = reader.result;
                        var formData = new FormData();

                        formData.append('file', file);
                        formData.append('root', object.root);

                        $.ajax({
                            url: <?php echo json_encode($router->route('mediaLibrary.add')) ?>,
                            type: "post",
                            data: formData,
                            dataType: 'json',
                            success: function (response) {
                                if(response.message) {
                                    object.app.showMessage(response.message[1], response.message[0]);
                                }

                                object.addFileToList(response.filename);
                                object.chooseFile(response.filename);
                                object.openMediaLibraryTab();

                                object.progressPercentage.innerHTML = "";
                                object.progressBar.style.width = "0%";
                                object.progressBar.setAttribute('aria-valuenow', 0);
                            },
                            error: function (response) {
                                if(response.responseJSON && response.responseJSON.message) {
                                    object.app.showMessage(response.responseJSON.message[1], "error");
                                }
                            },
                            contentType : false,
                            processData : false
                        });
                    }

                    reader.onerror = function(event) {
                        object.app.showMessage(
                            <?php echo json_encode(_('Lamentamos, mas houve uma falha na leitura do arquivo!')) ?>, 
                            'error'
                        );
                        reader.abort();
                    }

                    reader.onprogress = function(data) {
                        if(data.lengthComputable) {                                            
                            var progress = parseInt(((data.loaded / data.total) * 100), 10);
                            
                            object.progressBar.style.width = progress + "%";
                            object.progressBar.setAttribute('aria-valuenow', progress);
                            object.progressPercentage.innerHTML = progress + "%";
                        }
                    }
                }
            } else {
                var str = object.fileTypes.join(", ");
                object.app.showMessage(
                    <?php echo json_encode(
                        sprintf(
                            _('A extensão do arquivo que você tentou enviar não é permitida aqui! Extensões permitidas: %s'), 
                            '{extensions}'
                        )
                    ) ?>.replace('{extensions}', `${str}`), 
                    'error'
                );
            }
        }

        deleteFile(filename) 
        {
            var object = this;

            $.ajax({
                url: <?php echo json_encode($router->route('mediaLibrary.delete')) ?>,
                type: "delete",
                data: { 
                    root: this.root,
                    name: filename 
                },
                dataType: 'json',
                success: function (response) {
                    if(response.message) {
                        object.app.showMessage(response.message[1], response.message[0]);
                    }
                    
                    object.deleteFileFromList(filename);
                    if(object.choosenFile == filename) {
                        object.choosenFile = null;
                        object.setButtonStatus();
                    }
                },
                error: function (response) {
                    if(response.responseJSON && response.responseJSON.message) {
                        object.app.showMessage(response.responseJSON.message[1], response.responseJSON.message[0]);
                    }
                }
            });
        }

        chooseFile(filename) 
        {
            var allFiles = this.list.querySelectorAll("div[img-name]");
            allFiles.forEach(function (elem) {
                elem.querySelector("div.file-border").classList.remove("border-primary", "border");
            });

            this.choosenFile = filename;
            var file = this.list.querySelector("div[img-name='" + this.choosenFile + "']");
            file.querySelector("div.file-border").classList.add('border-primary', 'border');

            this.setButtonStatus();
        }

        checkFileExtension(filename) 
        {
            var extension = filename.split(".").pop();

            if(this.fileTypes.includes(extension)) {
                return true;
            } else {
                return false;
            }
        }

        addFileToList(file_name) 
        {
            if(this.checkFileExtension(file_name)) {
                var fileType = file_name.split(".").pop();
                var imagesTypes = ['jpg', 'jpeg', 'png', 'gif'];
                var object = this;

                let content;
                let div1 = document.createElement('div');
                let div2 = document.createElement('div');
                let div3 = document.createElement('div');
                let icon = document.createElement('i');
                let small = document.createElement('small');
        
                icon.setAttribute('class', "icofont-close text-danger");
                icon.style.fontSize = "2rem";
                
                if(imagesTypes.includes(fileType)) {
                    content = document.createElement('img');

                    content.setAttribute('src', `${this.path ? this.path + '/' : ''}${this.root}/${file_name}`);
                    content.setAttribute('class', 'img-thumbnail');
                    content.style.width = "112px";
                    content.style.height = "112px";
                } else {
                    content = document.createElement('i');
                    content.setAttribute('class', `${this.typesList[fileType].type} ${this.typesList[fileType].color} text-center mt-4`);
                    content.style.fontSize = "90px";
                    content.style.width = "112px";
                    content.style.height = "112px";
                }
                
                div2.setAttribute('class', 'bg-white p-0 m-0 border border-primary rounded delete');
                div2.style.position = 'absolute';
                div2.style.top = 0;
                div2.style.right = 0;
                div2.style.display = 'none';
        
                div1.setAttribute('class', 'mb-2 mr-2');
                div1.setAttribute('img-name', file_name);
                div1.style.position = 'relative';
                div1.style.cursor = 'pointer';
                div1.style.width = "120px";

                div3.setAttribute('class', 'file-border d-flex align-items-center align-middle');
                div3.style.width = "114px";
                div3.style.height = "114px";
                small.innerHTML = file_name;
        
                div2.appendChild(icon);
                div3.appendChild(content);
                div1.appendChild(div3);
                div1.appendChild(small);
                div1.appendChild(div2);
                
                div1.addEventListener('mouseover', function() {
                    div2.style.display = 'block';
                });
        
                div1.addEventListener('mouseleave', function() {
                    div2.style.display = 'none';
                });
        
                div2.addEventListener('click', function () {
                    div1.classList.add("bg-transparent");
                    object.deleteFile(file_name);
                });
        
                div1.addEventListener('click', function () {
                    var filename = div1.getAttribute("img-name");
                    object.chooseFile(filename);
                });
        
                this.list.insertBefore(div1, this.list.firstChild);
            }
        }

        deleteFileFromList(img_name) 
        {
            var elem = this.list.querySelector("div[img-name='" + img_name + "']");
            if(elem) elem.remove();
        }

        loadFiles(search = null, page = 1) 
        {
            var object = this;

            $.ajax({
                url: <?php echo json_encode($router->route('mediaLibrary.load')) ?>,
                type: "get",
                data: { 
                    root: object.root,
                    search: search,
                    page: page,
                    limit: object.pageLimit
                },
                dataType: 'json',
                success: function(response) {
                    if(response.message) {
                        object.app.showMessage(response.message[1], response.message[0]);
                    }

                    object.pagination.querySelector("ul.pagination").innerHTML = "";
                    object.list.innerHTML = "";

                    if(response.files) {
                        object.files = response.files;
                        for(var i = 0; i < object.files.length; i++) {
                            object.addFileToList(object.files[i]);
                        }
                    }

                    if(response.pages && response.pages > 1) {
                        object.setPagination(response.pages, page);
                    }
                },
                error: function (response) {
                    if(response.responseJSON && response.responseJSON.message) {
                        object.app.showMessage(response.responseJSON.message[1], response.responseJSON.message[0]);
                    }
                }
            });
        }

        setPagination(pages, curr_page) 
        {
            const object = this;
            if(pages) {
                const pagination = object.pagination.querySelector("ul.pagination");
                if(curr_page > 1) {
                    pagination.innerHTML += `
                        <li class="page-item">
                            <button class="page-link" aria-label="${<?php echo json_encode(_('Anterior')) ?>}" 
                                data-page="${curr_page - 1}">
                                <span aria-hidden="true">«</span><span class="sr-only">
                                    ${<?php echo json_encode(_('Anterior')) ?>}
                                </span>
                            </button>
                        </li>
                    `;
                }

                if(curr_page >= 6) {
                    for(var i = curr_page - 4; i <= pages && i <= curr_page + 5; i++) {
                        pagination.innerHTML += `
                            <li class="page-item ${curr_page == i ? "active" : ""}">
                                <button class="page-link" aria-label="Page ${i}" data-page="${i}">
                                    <span aria-hidden="true">${i}</span><span class="sr-only">Page ${i}</span>
                                </button>
                            </li>
                        `;
                    }
                } else {
                    for(var i = 1; i <= pages && i <= 10; i++) {
                        pagination.innerHTML += `
                            <li class="page-item ${curr_page == i ? "active" : ""}">
                                <button class="page-link" aria-label="Page ${i}" data-page="${i}">
                                    <span aria-hidden="true">${i}</span><span class="sr-only">Page ${i}</span>
                                </button>
                            </li>
                        `;
                    }
                }

                if(pages > curr_page) {
                    pagination.innerHTML += `
                        <li class="page-item">
                            <button class="page-link" aria-label="${<?php echo json_encode(_('Próxima')) ?>}" 
                                data-page="${parseInt(curr_page) + 1}">
                                <span aria-hidden="true">»</span><span class="sr-only">
                                    ${<?php echo json_encode(_('Próxima')) ?>}
                                </span>
                            </button>
                        </li>
                    `;
                }

                pagination.querySelectorAll("[data-page]").forEach(function (elem) {
                    elem.addEventListener("click", function () {
                        var pageNum = parseInt(elem.getAttribute("data-page"));
                        object.loadFiles(object.form.search.value, pageNum);
                    });
                });
            }
        }

        setButtonStatus() 
        {
            if(this.choosenFile) {
                this.chooseButton.disabled = false;
            } else {
                this.chooseButton.disabled = true;
            }
        }

        returnFilePath(filename) 
        {
            return this.root + "/" + filename;
        }
    }
</script>