$(function(){
    var prompt_input = "";
    var pwd = "/";
    var viewed = $.cookie('viewed_info');

    if(!viewed){
        $('.info').css('animation', 'blink 1s linear infinite');
        $('.info').on('mouseenter', function(){
            if(!viewed){
                viewed = true;
                $.cookie('viewed_info', true);
                $('.info').css('animation','');
            }
        });
    }

    $('.prompt').html(generate_ps());
    $('.prompt').focus();
    $('.prompt').on('keyup', function(event){
        if (event.key.match(/^[\d\w\-\/\. ]$/i)){
            prompt_input += event.key;
            value = $(this).html();
            $(this).html(" ");
            $(this).html(value + event.key);
        }

        if(event.keyCode == 8){
            remove_char(this);
        }

        if(event.keyCode == 13){
            command = prompt_input;
            prompt_input = "";
            run(command);
        }
    });

    function remove_char(elment){
        if(prompt_input.length == 0)
                return;
            prompt_input = prompt_input.substring(0, prompt_input.length - 1);
            value = $(elment).html().substring(0, $(elment).html().length -1);
            $(elment).html(" ");
            $(elment).html(value);
    }

    function next_line(){
        elm = $('.prompt');
        value = elm.html() + "\r\n";
        elm.html(value);
    }

    function prompt(){ 
        elm = $('.prompt');
        value = elm.html();
        elm.html(value + generate_ps());
    }

    function generate_ps(){
        str = make_element("\\u@philosophia: ","#43A047") 
        + make_element(" \\w","white") + make_element(" $ ", "#4FC3F7");

        return str.replace('\\u', get_username()).replace('\\w',pwd);
    }

    function run(command){
        if(!command){
            next_line();
            prompt();
            return;
        }
        command_name = command.split(' ')[0];
        switch (command_name) {
            case 'clear':
                $('.prompt').html(generate_ps());
                break;
            case 'login':
                password = window.prompt("Enter your password");
                return ajax(command, {password:password});
            case 'passwd':
                password = window.prompt("Enter your new password");
                return ajax(command, {password:password});
            case 'register':
                password = window.prompt("Enter your password");
                return ajax(command, {password:password});
            default:
                return ajax(command);
        }
    }
    
    function get_username(){
        return $.cookie('username') ?? 'guest';
    }
    
    function ajax(command, data=null){
        url = "/logos/command";

        data = {command:command, pwd:pwd, ...data};
        $.post({
            type: "POST",
            url: url,
            dataType: 'json',
            async:false,
            data: data,
            crossDomain: true,
            xhrFields: { withCredentials: true },
            success:function(data){
                print_result(data);
            },
            error:function(xhr){
                next_line();
                print_error(xhr.responseJSON)
                prompt();
            }
        });
    }

    function print_result(result){
        next_line();
        wait = false;
        switch(result['_command']){
            case 'cd':
                pwd = result['_pwd'];
                break;
            case 'ls':
                generate_listing(result);
                break;
            case 'login':
                if(result.username)
                    $.cookie('username',result.username, {expires:30});
                break;
            case 'logout':
                $.removeCookie('username');
                break;
            case 'notepad':
                notepad({
                    title: result.title,
                    body: result.body,
                    onSave:function(data){
                        if(result.id){
                            data.update = true;
                            data.id = result.id;
                            ajax('write', data);
                            notepad_close();
                        }
                        else
                            ajax('write', data);
                    },
                    onClose:function(){
                        prompt();
                    }
                });
                wait = true;
                break;
            
            case 'view':
                viewer({title:result.title, body:result.body});
                break;
            
            default:
                if(result.message){
                    print(result.message, "green");
                }
                if(result.list){
                    generate_listing(result);
                }
                if(result.data){
                    print(syntaxHighlight(result.data));
                }
                console.log(result);
        }
        if(!wait){
            prompt();
        }
    }

    function print_error(result){
        elm = $('.prompt');
        value = elm.html() + '<span class="red">'+result['message']+'</span>';
        elm.html(value);
        next_line();
    }

    function print(text, color="white"){
        elm = $('.prompt');
        value = elm.html() + `<span style="color:${color}">${text}</span>`;
        elm.html(value);
        next_line();
    }

    function generate_listing(data){
        list = $('<ul class="list"></ul>');
        if(data.categories){
            data.categories.forEach(element => {
                item = $('<li class="category"></li>').html(element.name+"/");
                list.append(item);
            });
        }
        if(data.posts){
            data.posts.forEach(element => {
                item = $('<li class="post"></li>').html(element.title);
                list.append(item);
            });
        }
        if(data.list){
            Object.keys(data.list).forEach(element => {
                item = $('<li class="post"></li>').html(element);
                list.append(item);
            })
        }
        $('.prompt').append(list);
    }

    function make_element(text, color){
        return `<span style="color:${color}">${text}</span>`;
    }

    function syntaxHighlight(json) {
        if (typeof json != 'string') {
             json = JSON.stringify(json, undefined, 2);
        }
        json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
            var cls = 'number';
            if (/^"/.test(match)) {
                if (/:$/.test(match)) {
                    cls = 'key';
                } else {
                    cls = 'string';
                }
            } else if (/true|false/.test(match)) {
                cls = 'boolean';
            } else if (/null/.test(match)) {
                cls = 'null';
            }
            return '<span class="' + cls + '">' + match + '</span>';
        });
    }

});

