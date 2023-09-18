const express = require('express');
const mysql = require('mysql2');
const app = express();
const http = require('http');
const server = http.createServer(app);
const { Server } = require("socket.io");
const io = new Server(server);
var XMLHttpRequest = require('xhr2');

const users = {};

//Connect SQL database
const connection = mysql.createConnection({
    host: '127.0.0.1',
    port : 3306,
    user: 'root',
    password: 'password',
    database: 'test_db_003'
});

//SQL query
function sqlquery(sql, par, msg){
    connection.query(sql, par, function (err, result){
        if(err) throw err;
        if(msg) console.log(msg);
    });
}

//Get now time
function gettime(){
    const date = new Date();
    const nowtime = date.getFullYear() + "/" + (date.getMonth() + 1) + "/" + date.getDate() + " " + date.getHours() + ":" + date.getMinutes() + ":" + date.getSeconds();
    return nowtime;
}

app.use(express.static(__dirname + "/public"));

app.use(function (req, res, next){
    res.header("Access-Control-Allow-Origin", "*");
    res.header("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept");
    next();
});

io.on('connection', (socket) => {
    var room = '';
    var tag = Math.floor(Math.random() * 8999 + 1000);
    socket.on('ROOM_ID', (id) => {
        if(id){
            room = id;
            socket.join(room);
            sqlquery("CREATE TABLE IF NOT EXISTS ?? (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,user VARCHAR(256), message VARCHAR(256), time VARCHAR(32), status VARCHAR(32), remark VARCHAR(32))", ["tb_" + room + "_"]);
        }
    });
    socket.on('MESSAGE_TO_SERVER', (msg, time) => {
        io.to(room).emit('MESSAGE_TO_CLIENT', users[socket.id], msg, tag);
        sqlquery("INSERT INTO ?? SET user=?, message=?, time=?", ["tb_" + room + "_", users[socket.id] + " #" + tag, msg, time]);
        console.log("[" + room + "] " + users[socket.id] + ' >> ' + msg);

        if(room.startsWith("public_")){
            var json_data = {
                room: room,
                user: users[socket.id] + " #" + tag,
                message: msg,
                time: time
            };
    
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "http://127.0.0.1:10014/", true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.send(JSON.stringify(json_data));
        }
    });
    socket.on('USER_JOIN', (name, time) => {
        io.to(room).emit('USER_JOIN_MESSAGE', room, name, tag);
        users[socket.id] = name;
        console.log("[" + room + "] " + users[socket.id] + " joined.");
        sqlquery("INSERT INTO ?? SET user=?, message=?, time=?, status=?, remark=?", ["tb_" + room + "_", users[socket.id] + " #" + tag, "", time, "USER_JOIN", room]);
        io.to(room).emit('USER_UPDATE', room, Object.keys(users).length);
        connection.query("SELECT * FROM ?? ", ["tb_" + room + "_"], function (err, result){
            io.to(socket.id).emit('LOAD_MESSAGE', result);
        });
    });
    socket.on('disconnect', () => {
        if(users[socket.id]){
            io.to(room).emit('USER_EXIT_MESSAGE', room, users[socket.id], tag);
            console.log("[" + room + "] " + users[socket.id] + " left.");
            sqlquery("INSERT INTO ?? SET user=?, message=?, time=?, status=?, remark=?", ["tb_" + room + "_", users[socket.id] + " #" + tag, "", gettime(), "USER_EXIT", room]);
            delete users[socket.id];
            if(Object.keys(users).length == 0){ console.log("[" + room + "] is now empty.");}
            io.to(room).emit('USER_UPDATE', room, Object.keys(users).length);
        }
    });
});

server.listen(10013, () => {
    console.log('Listening on localhost:10013.');
});