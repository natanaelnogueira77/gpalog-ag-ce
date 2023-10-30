<script>
    class FileSelector 
    {
        #currentId = 0;
        #filepaths = null;
        #type = 1;
        #listElem = null;
        #onAdd = null;
        #onAddSuccess = null;
        #onEdit = null;
        #onEditSuccess = null;
        #onRemove = null;
        #onMediaLibrarySuccess = null;
        #typesList = {
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
        mediaLibrary = null;

        constructor(listElem, mediaLibrary) 
        {
            this.#listElem = $(listElem);
            this.mediaLibrary = mediaLibrary;
        }

        setAsSingle() 
        {
            this.#type = 1;
            this.#filepaths = {};
            return this;
        }

        setAsMultiple() 
        {
            this.#type = 2;
            this.#filepaths = [];
            return this;
        }

        setOnAdd(func = function () {}) 
        {
            this.#onAdd = func;
            return this;
        }

        setOnAddSuccess(func = function () {}) 
        {
            this.#onAddSuccess = func;
            return this;
        }

        setOnEdit(func = function () {}) 
        {
            this.#onEdit = func;
            return this;
        }

        setOnEditSuccess(func = function () {}) 
        {
            this.#onEditSuccess = func;
            return this;
        }

        setOnRemove(func = function () {}) 
        {
            this.#onRemove = func;
            return this;
        }

        loadFiles(filepaths) 
        {
            if(filepaths) {
                if(this.#type == 1) {
                    this.#addToList(this.#getNextId(), filepaths.uri, filepaths.url);
                } else if(this.#type == 2) {
                    for(const [index, value] of Object.entries(filepaths)) {
                        this.#addToList(this.#getNextId(), value.uri, value.url);
                    }
                }
            }

            return this;
        }

        cleanFiles() 
        {
            this.#filepaths = null;
            this.#currentId = 0;
            return this;
        }

        getList() 
        {
            return this.#filepaths;
        }

        getURIList() 
        {
            if(this.#filepaths) {
                if(this.#type == 1) {
                    return this.#filepaths.uri;
                } else if(this.#type == 2) {
                    return this.#filepaths.map((file) => file.uri);
                }
            }

            return null;
        }

        getURLList() 
        {
            if(this.#filepaths) {
                if(this.#type == 1) {
                    return this.#filepaths.url;
                } else if(this.#type == 2) {
                    return this.#filepaths.map((file) => file.url);
                }
            }

            return null;
        }

        render() 
        {
            this.#listElem.children().remove();
            this.#listElem.append(this.#getListWrapperElement());
            this.#listElem.children(":first").append(this.#getAddSlotElement());

            if(this.#filepaths) {
                if(this.#type == 1) {
                    var filepaths = [this.#filepaths];
                } else if(this.#type == 2) {
                    var filepaths = this.#filepaths;
                }

                for(const [index, value] of Object.entries(filepaths)) {
                    this.#addFile(value.id, value.uri, value.url);
                }
            }
            
            return this;
        }

        addToSelector(path) 
        {
            this.#addFile(this.#getNextId(), path, `${this.mediaLibrary.path}/${path}`);
            this.#addToList(this.#getCurrentId(), path, `${this.mediaLibrary.path}/${path}`);

            return this;
        }

        updateOnSelector(elem, id, path) 
        {
            elem.children(":last").remove();
            elem.append(this.#getFilePreview(path, `${this.mediaLibrary.path}/${path}`));
            this.#editFromList(id, path, `${this.mediaLibrary.path}/${path}`);

            return this;
        }

        removeOnSelector(elem, id, fileURI, fileURL) 
        {
            if(this.#type == 1) {
                elem.remove();
                this.#listElem.children(":first").append(this.#getAddSlotElement());
                this.#removeFromList(id);
            } else if(this.#type == 2) {
                elem.remove();
                this.#removeFromList(id);
            }

            return this;
        }

        #getNextId() 
        {
            this.#currentId++;
            return this.#currentId;
        }

        #getCurrentId() 
        {
            return this.#currentId;
        }

        #addToList(id, fileURI, fileURL) 
        {
            if(this.#type == 1) {
                this.#filepaths = {
                    id: id,
                    uri: fileURI,
                    url: fileURL
                };
            } else if(this.#type == 2) {
                if(!this.#filepaths) {
                    this.#filepaths = [];
                }

                this.#filepaths.push({
                    id: id,
                    uri: fileURI,
                    url: fileURL
                });
            }

            return this;
        }
        
        #editFromList(id, fileURI, fileURL) 
        {
            if(this.#type == 1) {
                this.#filepaths.uri = fileURI;
                this.#filepaths.url = fileURL;
            } else if(this.#type == 2) {
                const offset = this.#filepaths.findIndex((i) => {
                    return i.id === id;
                });
                this.#filepaths[offset].uri = fileURI;
                this.#filepaths[offset].url = fileURL;
            }
            
            return this;
        }

        #removeFromList(id) 
        {
            if(this.#type == 1) {
                this.#filepaths = {};
            } else if(this.#type == 2) {
                const offset = this.#filepaths.findIndex((i) => {
                    return i.id === id;
                });
                this.#filepaths.splice(offset, 1);
            }
            return this;
        }

        #addFile(id, fileURI, fileURL) 
        {
            const object = this;
            const elem = object.#getFileElement(fileURI, fileURL);

            if(object.#type == 1) {
                elem.find("[data-act=edit]").click(function () {
                    if(object.#onEdit) {
                        object.#onEdit(object, elem, id);
                    } else {
                        object.mediaLibrary.setSuccess(function (path) {
                            if(object.#onEditSuccess) {
                                object.#onEditSuccess(object, elem, id, path);
                            } else {
                                object.updateOnSelector(elem, id, path);
                            }
                        }).open();
                    }
                });

                elem.find("[data-act=remove]").click(function () {
                    if(object.#onRemove) {
                        object.#onRemove(object, elem, id, fileURI, fileURL);
                    } else {
                        object.removeOnSelector(elem, id, fileURI, fileURL);
                    }
                });

                object.#listElem.children(":first").children().remove();
                object.#listElem.children(":first").append(elem);
            } else if(object.#type == 2) {
                elem.find("[data-act=edit]").click(function () {
                    if(object.#onEdit) {
                        object.#onEdit(object, elem, id);
                    } else {
                        object.mediaLibrary.setSuccess(function (path) {
                            if(object.#onEditSuccess) {
                                object.#onEditSuccess(object, elem, id, path);
                            } else {
                                object.updateOnSelector(elem, id, path);
                            }
                        }).open();
                    }
                });

                elem.find("[data-act=remove]").click(function () {
                    if(object.#onRemove) {
                        object.#onRemove(object, elem, id, fileURI, fileURL);
                    } else {
                        object.removeOnSelector(elem, id, fileURI, fileURL);
                    }
                });

                elem.insertBefore(object.#listElem.children(":first").children(":last"));
            }
        }

        #getListWrapperElement() 
        {
            return $(`<div class="d-flex flex-wrap justify-content-center"></div>`);
        }

        #getFileElement(fileURI, fileURL) 
        {
            const newFileElement = $(`
                <div style="position: relative; width: 114px; height: auto; border-radius: 15px;" class="mx-2 mb-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" 
                        style="position: absolute; cursor: pointer;" data-act="edit">
                        <i class="icofont-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" 
                        style="position: absolute; cursor: pointer; left: 30px;" data-act="remove">
                        <i class="icofont-trash"></i>
                    </button>
                </div>
            `);
            newFileElement.append(this.#getFilePreview(fileURI, fileURL));
            return newFileElement;
        }

        #getFilePreview(fileURI, fileURL) 
        {
            var extension = fileURL.split(".").pop();
            if(['jpg', 'jpeg', 'png', 'gif'].includes(extension)) {
                return $(`
                    <div>
                        <img class="img-thumbnail" style="width: 114px; height: 114px;" src="${fileURL}">
                        <small style="word-break: break-word;">${fileURI.split('/').pop()}</small>
                    </div>
                `);
            } else {
                return $(`
                    <div>
                        <div class="img-thumbnail d-flex justify-content-around align-items-center" style="width: 114px; height: 114px;">
                            <i class="${this.#typesList[extension]?.type} ${this.#typesList[extension]?.color}" style="font-size: 90px;"></i>
                        </div>
                        <small style="word-break: break-word;">${fileURI.split('/').pop()}</small>
                    </div>
                `);
            }
        }

        #getAddSlotElement() 
        {
            const object = this;
            const elem = $(`
                <div style="height: 114px; width: 114px; position: relative; cursor: pointer;" 
                    class="img-thumbnail d-flex justify-content-around align-items-center mx-2 mb-2">
                    <i class="icofont-plus text-primary"></i>
                </div>
            `);

            elem.click(function () {
                if(object.#onAdd) {
                    object.#onAdd(object, elem, object.#getNextId());
                } else {
                    object.mediaLibrary.setSuccess(function (path) {
                        if(object.#onAddSuccess) {
                            object.#onAddSuccess(object, elem, path);
                        } else {
                            object.addToSelector(path);
                        }
                    }).open();
                }
            });

            return elem;
        }
    }
</script>