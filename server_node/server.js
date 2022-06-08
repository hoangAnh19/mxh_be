const httpServer = require("http").createServer();
var io = require('socket.io')(httpServer, {
    cors: {
      origin: "*",
      methods: ["GET", "POST","PUT"]
    }
  })
  httpServer.listen(6003);
console.log('Conncet to port 6003')
var list_online = [];
var list_friend = [];
io.on('error', function ( socket ) {
    console.log('error')
    console.log(socket)
})
io.on('connection', function( socket ) {
    console.log('Có người vừa kết nối')



    socket.on("disconnect", () => {
        console.log("có nguwofi vừa ngắt kết nối", socket.id); // "ping timeout"

        id = JSON.parse(Buffer.from(socket.handshake.auth.jwt.split('.')[1], 'base64')).sub;
        console.log('-------------------')
        console.log(id)
        console.log(list_online[id])
        if (!list_online[id]) return
        list_online[id].forEach((idClient, index) => {
          console.log(idClient, socket.id)
          if (idClient === socket.id) {
            list_online[id].splice(index,1)
            return
          }
        })
        if (!list_online[id].length) {
          list_online[id] = null

          list_friend[id].forEach(user_id => {
            if (list_online[user_id]) {
              list_online[user_id].forEach(clientId => {
                io.to(clientId).emit('offline', id)
              })
            }


          })
        }
      });
})
const e = require("express");
var Redis = require('ioredis')
var redis = new Redis(6379)
redis.psubscribe("*", function(error, count) {
    //
})
redis.on('pmessage', function(partner, channel, message) {
  message = JSON.parse(message)
   switch (channel) {
     case "laravel_database_chat":
       console.log(message)
        receiver = message.data.message.receiver_id
        if (!list_online[receiver]) list_online[receiver] = []
        list_online[receiver].forEach(clientId => {
          io.to(clientId).emit(channel + ":" + message.event, message.data.message)

        })
        console.log('Send')
       break
      case "laravel_database_online_":
        var id = message.data.message.user
        client_id = message.data.message.client_id
        if ((list_online[id]??[]).length) {
           if (list_online[id].includes(message.data.message.client_id)) return
         list_online[id].push(client_id)
        } else {
          list_online[id] = [client_id]
        }
        list = Object.values(message.data.message.list);
        list_friend[message.data.message.user] = list;
          list.forEach(user_id => {
         if (list_online[user_id] && list_online[user_id].length) {
          list_online[user_id].forEach(clientId => {
            console.log( channel + ":" + message.event)
            io.to(clientId).emit(channel + ":" + message.event, message.data.message.user)
          })
          list_online[id].forEach(clientId => {
            io.to(clientId).emit(channel + ":" + message.event, user_id)
          })
         }
       })
       console.log('Online')
        break;
      default:
        break
   }
   console.log(list_online)


})

