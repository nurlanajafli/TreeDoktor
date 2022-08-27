<?php

if(!empty($_POST)) {
    $fp = fopen('command', 'w');
    fwrite($fp, http_build_query($_POST));
    fclose($fp);
    die;
}
?>
<script src="https://treedoctors.ca/js/socket.io-1.4.5.js"></script>
<script src="https://td.onlineoffice.io/assets/js/jquery.js"></script>
<script src="zeu.min.js"></script>
<style>
    body {
        padding: 5px 10px;
    }
    .console-overley {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: repeating-linear-gradient(0deg, rgba(0,0,0,0.15), rgba(0,0,0,0.15) 1px, transparent 1px, transparent 2px);
        pointer-events: none;
    }
    .console-block {
        width: 80%;
        float: left;
    }
    .console-block-p {
        padding: 5px 10px;
    }
    .console {
        position: relative;
        background-color: black;
        background-image: radial-gradient(rgba(0,150,0,0.75), black 120%);
        height: 500px;
        margin: 0;
        overflow: auto;
        padding: 2rem;
        color: white;
        font: 1.3rem Inconsolata, monospace;
        text-shadow: 0 0 5px #c8c8c8;
    }
    ::selection {
       background: #0080ff;
        text-shadow: none;
    }
    .console pre {
        margin: 0;
        padding: 10px 20px;
    }
    .row {margin-left: -15px; margin-right: -15px}
    .form {width: 20%;
        float: left;}
    .form form {
        padding: 5px; 10px;}
    .form div {width: 100%;
        height: 25px; clear: both;}
    .form select {
        float: right;}
    .form button {
        float: right; margin-top: 15px;}
</style>

<body>
    <div class="row">
        <div class="form">
            <form action="" method="POST" id="command">
                <div>
                    <label for="vdr">Диспетчерский режим</label>
                    <select name="vdr" id="vdr">
                        <option value="0">Off</option>
                        <option value="1">On</option>
                    </select>
                </div>
                <div>
                    <label for="vzu">Зеленая Улица</label>
                    <select name="vzu" id="vzu">
                        <option value="0">Off</option>
                        <option value="1">On</option>
                    </select>
                </div>
                <div>
                    <label for="vtek">Коррекция ТЭК</label>
                    <select name="vtek" id="vtek">
                        <option value="0">Off</option>
                        <option value="1">On</option>
                    </select>
                </div>
                <div>
                    <label for="vf">Фаза №</label>
                    <select name="vf" id="vf">
                        <option value="0">Off</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                        <option value="7">7</option>
                        <option value="8">8</option>
                    </select>
                </div>
                <?php
                if(!empty($_POST) && $_POST) {
                    file_put_contents('command', http_build_query($_POST));
                }
                ?>
                <div>
                    <label for="vjm">Мигающий желтый</label>
                    <select name="vjm" id="vjm">
                        <option value="0">Off</option>
                        <option value="1">On</option>
                    </select>
                </div>
                <div>
                    <label for="vos">Светофор отключен</label>
                    <select name="vos" id="vos">
                        <option value="0">Off</option>
                        <option value="1">On</option>
                    </select>
                </div>
                <div>
                    <button type="submit">Отпрвить</button>
                </div>
            </form>
            <h3 style="text-align: center; margin-top: 50px;">
                Состояние<br>
                <span style="text-decoration: underline; white-space: pre-line; display: inline-block; margin-top: 10px;" id="state"> - </span>
            </h3>
        </div>
        <div class="console-block">
            <canvas id="heartbeat" width="100%" height="100" style="margin-left: 10px;"></canvas>
            <div class="console-block-p">
                <div class="console">
                    <div class="console-overley">
                        <pre></pre>
                    </div>
                </div>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
    <script>
        var heartbeat = new zeu.Heartbeat('heartbeat', {
            viewWidth: $('.console-block-p').width(),
            speed: 1,
            fontColor: '#343a42',
            maxQueueCapacity: 1000
        });
        var prevRequestData = [];
        var prevResponseData = [];
        var state = false;
        var connected = false;
        var wsaddress = 'http://138.197.159.118:15123';
        ws = io.connect(wsaddress);

        function checkConnection() {
            setTimeout(function () {
                if(connected == true)
                    connected = false;
                else
                    $('#state').text('Отсуствует связь с модулем');
                checkConnection();
            }, 3000)
        }
        
        function setState(request) {
            state = '';
            if(request.dkf == 1)
                state += 'Ошибка ДК' + "\r\n";
            if(request.ru == 1)
                state += 'Местное ручное управление' + "\r\n";
            if(request.dr == 1)
                state += 'Диспетчерский режим' + "\r\n";
            if(request.os == 1)
                state += 'Светофор отключен' + "\r\n";
            if(request.jm == 1)
                state += 'Желтый мигающий' + "\r\n";
            if(request.zu == 1)
                state += 'Зеленая улица' + "\r\n";
            if(request.zzu == 1)
                state += 'Запрос зеленой улицы' + "\r\n";
            if(request.f <= 8 && request.f >= 1)
                state += 'Включена фаза №' + "\r\n";
            if(request.prt == 1)
                state += 'Промежуточный такт' + "\r\n";

            if(!state)
                state = 'Отсутствует связь с БУ';
            $('#state').text(state);
        }

        checkConnection();

        ws.on('bridge', function(data) {
            connected = true;
            var num = 0;
            var reqLog = '{ ';
            setState(data.request);

            $.each(data.request, function(index, value) {
                reqLog += num ? ', ' : '';
                reqLog += index + ':' + value;
                num++
            });
            reqLog += ' }';

            if(JSON.stringify(data.request)!=JSON.stringify(prevRequestData)) {
                prevRequestData = data.request;
                heartbeat.beat({
                    color: '#ff0000',
                    space: 0
                });

            }

            var respLog = '{ ';
            num = 0;
            $.each(data.request, function(index, value) {
                respLog += num ? ', ' : '';
                respLog += index + ':' + value;
                num++
            });
            respLog += ' }';

            var autoScroll = $('.console').scrollTop() + 530 >= $('.console pre').height() || $('.console pre').height() < 700
            if($('.console pre .call').length)
                $('.console pre').append("\r\n<output >-----------------------------------------------------------------</output>\r\n");
            $('.console pre').append("\r\n" + "<output  class='call'>Request "+data.datetime+": \r\n\r\nData: "+reqLog+"</output>");
            if(autoScroll)
                $('.console').scrollTop($('.console').prop('scrollHeight'));
        });


        $(document).ready(function () {
            $('#command').submit(function () {
                $.post('form.php', $(this).serialize());
                return false;
            });
        });
        (function () {
            var canvas = document.getElementById('heartbeat'),
            context = canvas.getContext('2d');
            window.addEventListener('resize', resizeCanvas, false);

            function resizeCanvas() {
                canvas.width = $('.console-block-p').width();
                canvas.height = 100;
                drawStuff();
            }
            resizeCanvas();
            function drawStuff() {
                
            }
        })();
    </script>
</body>
