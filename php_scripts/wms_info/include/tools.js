                            var headers = document.getElementsByTagName("h2");
                            registerEvents(headers);

                            var divs = document.getElementById("divLayers").getElementsByTagName("div");
                            for (var i = 0; i < divs.length; i++){
                                if (divs[i].className.indexOf("header") !== -1)
                                    divs[i].addEventListener("click", toggleDiv, false);
                            }

                            function registerEvents(elements){
                                for (var i = 0; i < elements.length; i++){
                                    elements[i].addEventListener("click", toggleDiv, false);
                                }
                            }

                            function toggleDiv(e){
                                var header = e.currentTarget;
                                var div = document.getElementById(header.id.replace(/header/,"div"));
                                if (div){
                                    var expand = (div.style.display == "none");
                                    div.style.display = (expand) ? "block" : "none";
                                    var from = (expand) ? "collapsed" : "expanded";
                                    var to = (expand) ? "expanded" : "collapsed";
                                    var re = new RegExp(from,"g");
                                    header.className = header.className.replace(re,to);
                                }
                            }