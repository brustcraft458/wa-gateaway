import { Client } from 'whatsapp-web.js';
import QRCode from 'qrcode';
import { putFetch, getFetch } from './server.js';

const client = new Client({
    webVersionCache: {
      type: "remote",
      remotePath:
        "https://raw.githubusercontent.com/wppconnect-team/wa-version/main/html/2.3000.1014580163-alpha.html",
    },
});

var status = {
    client: false
}
var WaWebUserList = []

client.on('qr', (qr) => {
    // Generate and scan this code with your phone
    console.log('QR RECEIVED', qr);
    QRCode.toFile("./qrcode.png", qr)
});

client.on('ready', () => {
    console.log('Client is ready!');
    status.client = true
});

client.on('message', msg => {
    if (msg.body == "ping") {
        msg.reply("pong")
    }
});

client.on('message_ack', (msg, ack) => {
    const ackMapping = [
        'msg_sent',   // 0
        'msg_sent_to_server', // 1
        'msg_delivered', // 2
        'msg_read',   // 3
        'msg_played'  // 4
    ]
    const ackStatus = ackMapping[ack] || `unknown_status ${ack}`
    const ackId = msg.id.id

    WaWebUserList.forEach(whatsapp => {
        const id = whatsapp.lastMessage.id.id

        if (ackId == id) {
            if (ackStatus == 'msg_delivered') {
                whatsapp.updateServerStatus('success')
            }
        }
    })
})

function waitClient() {
    client.initialize();

    return new Promise((resolve) => {
        const interval = setInterval(() => {
            if (status.client) {
                resolve(true);
                clearInterval(interval)
            }
        }, 1000)
    })
}

async function getAllMessage(status) {
    let respone = await getFetch(`/messages?status=${status}`)
    
    try {
        let data = respone.data
        return data
    } catch (err) {
        return null   
    }
}

class WaWebUser {
    constructor(phone, userId) {
        this.isValid = false
        this.phone = phone
        this.userId = userId
        this.chatId = `${phone}@c.us`
        this.lastMessage = null
    }

    validated() {
        if (this.isValid) {return true}

        const isPhoneValid = (phone) => {
            if (isNaN(phone)) {return false}

            const length = phone.length;
            return length >= 8 && length <= 14;
        }

        if (!isPhoneValid(this.phone)) {
            return false
        }

        this.isValid = true
        WaWebUserList.push(this)
        return true
    }

    async sendMessage(text) {
        let lastMessage = await client.sendMessage(this.chatId, text)
        this.lastMessage = lastMessage

        await this.updateServerStatus('sending')
    }

    async updateServerStatus(status) {
        await putFetch(`/messages/${this.userId}`, {'status': status})
        console.log(`message status: ${this.phone} => ${status}`)
    }
}

export {waitClient, getAllMessage, WaWebUser}