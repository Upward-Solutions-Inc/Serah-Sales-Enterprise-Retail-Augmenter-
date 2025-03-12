<template>
    <div class="clock-in-out-container">
        <h1>DTR - Clock In/Out</h1>
        <p v-if="lastAction" class="last-action">Last Action: {{ lastAction }}</p>

        <button @click="clockIn" class="btn btn-success" :disabled="isClocking">
            <i class="fas fa-sign-in-alt"></i> Clock In
        </button>
        <button @click="clockOut" class="btn btn-danger" :disabled="isClocking">
            <i class="fas fa-sign-out-alt"></i> Clock Out
        </button>

        <p v-if="statusMessage" class="status-message">{{ statusMessage }}</p>
    </div>
</template>

<script>
import axios from "axios";

export default {
    data() {
        return {
            statusMessage: "",
            lastAction: "",
            isClocking: false,
        };
    },
    methods: {
        async clockIn() {
            this.isClocking = true;
            try {
                let response = await axios.post("/dtr/clock-in");
                this.statusMessage = response.data.message;
                this.lastAction = `Clocked In at ${response.data.timestamp}`;
            } catch (error) {
                this.statusMessage = "Error: Could not Clock In.";
            }
            this.isClocking = false;
        },
        async clockOut() {
            this.isClocking = true;
            try {
                let response = await axios.post("/dtr/clock-out");
                this.statusMessage = response.data.message;
                this.lastAction = `Clocked Out at ${response.data.timestamp}`;
            } catch (error) {
                this.statusMessage = "Error: Could not Clock Out.";
            }
            this.isClocking = false;
        },
    },
};
</script>

<style scoped>
.clock-in-out-container {
    text-align: center;
    max-width: 400px;
    margin: auto;
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}
h1 {
    margin-bottom: 20px;
}
.btn {
    display: block;
    width: 100%;
    margin: 10px 0;
    padding: 10px;
    font-size: 18px;
    cursor: pointer;
}
.btn-success {
    background-color: #28a745;
    color: white;
    border: none;
}
.btn-danger {
    background-color: #dc3545;
    color: white;
    border: none;
}
.last-action {
    font-size: 14px;
    color: #555;
}
.status-message {
    margin-top: 10px;
    font-weight: bold;
    color: #333;
}
</style>