const httpServer = require("http").createServer();
var io = require("socket.io")(httpServer, {
    cors: {
        origin: "*",
        methods: ["GET", "POST", "PUT"],
    },
});
httpServer.listen(6003);
console.log("Conncet to port 6003");
var list_online = [];
var list_user = [];
io.on("error", function (socket) {
    console.log("error");
    console.log(socket);
});
io.on("connection", function (socket) {
    console.log("Có người vừa kết nối", socket.id);
    console.log("list online", this.list_online);
    socket.on("disconnect", () => {
        console.log("có người vừa ngắt kết nối", socket.id); // "ping timeout"

        if (!list_online[id]) return;
        list_online[id].forEach((clientId, index) => {
            if (clientId === socket.id) {
                list_online[id].splice(index, 1);
                return;
            }
        });
        if (!list_online[id].length) {
            (list_user[id] ?? []).forEach((user_id) => {
                if (list_online[user_id]) {
                    list_online[user_id].forEach((clientId) => {
                        io.to(clientId).emit("offline", id);
                    });
                }
            });
        }
    });
});
const e = require("express");
var Redis = require("ioredis");
var redis = new Redis(6379);
redis.psubscribe("*", function (error, count) {
    //
});
redis.on("pmessage", function (partner, channel, message) {
    message = JSON.parse(message);
    switch (channel) {
        case "laravel_database_post_channel":
            console.log("message", message);
            io.emit(channel + ":" + message.event, message.data.message);
            break;
        case "laravel_database_comment_channel":
            console.log("message", message);
            io.emit(channel + ":" + message.event, message.data.message);
            break;

        case "laravel_database_chat":
            console.log("message chat", message);
            receiver = message.data.message.receiver_id;
            if (!list_online[receiver]) list_online[receiver] = [];
            list_online[receiver].forEach((clientId) => {
                io.to(clientId).emit(
                    channel + ":" + message.event,
                    message.data.message
                );
                console.log("da gui message");
            });
            break;
        case "laravel_database_online_channel":
            var id = message.data.message.user;
            client_id = message.data.message.client_id;
            if ((list_online[id] ?? []).length) {
                if (list_online[id].includes(message.data.message.client_id))
                    return;
                list_online[id].push(client_id);
            } else {
                list_online[id] = [client_id];
            }
            list = Object.values(message.data.message.list);
            list_user[message.data.message.user] = list;
            list.forEach((user_id) => {
                if (list_online[user_id] && list_online[user_id].length) {
                    list_online[user_id].forEach((clientId) => {
                        io.to(clientId).emit(
                            channel + ":" + message.event,
                            message.data.message.user
                        );
                    });
                    list_online[id].forEach((clientId) => {
                        io.to(clientId).emit(
                            channel + ":" + message.event,
                            user_id
                        );
                    });
                }
            });
            // console.log("Online");
            break;
        default:
            break;
    }
});
