import { waitClient, getAllMessage, WaWebUser } from "./controller/message.js";
const skipFatalLog = false

function sleep(ms) {
    return new Promise((resolve) => {
        setTimeout(() => {resolve(true)}, ms)
    })
}

async function start() {
    await waitClient()
    
    while (true) {
        try {
            await loop()
        } catch (error) {
            if (!skipFatalLog) {
                console.log(error)
                console.log("Something Error, Restarting...")
            }
        }
        await sleep(1000)
    }
}

async function loop() {
    let pendings = await getAllMessage('pending')
    if (!pendings) {return}

    for (let i = 0; i < pendings.length; i++) {
        let pending = pendings[i];

        // Send Message
        let whatsapp = new WaWebUser(pending.phone, pending.id)
        if (whatsapp.validated()) {
            await whatsapp.sendMessage(pending.text)
        } else {
            await whatsapp.updateServerStatus('failed')
        }
    }
}

start()