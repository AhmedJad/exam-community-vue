<template>
  <select
    @change="getMessages"
    v-model="room_id"
    class="form-select"
    aria-label="Default select example"
  >
    <option v-for="room in rooms" :key="room.id" :value="room.id">
      {{ room.name }}
    </option>
  </select>
  <div class="border message">
    <p v-for="message in messages" :key="message.id">
      {{ message.user.first_name + " " + message.user.last_name }} : {{ message.message }}
    </p>
  </div>
  <input
    @keyup.enter="newMessage"
    v-model="message"
    class="form-control"
    placeholder="Type new message..."
  />
</template>

<script>
import { reactive, toRefs, watch } from "vue-demi";
import chatClient from "../../shared/http-clients/chat-client";
import TokenUtil from "../../shared/utils/token-util";
import tokenUtil from "../../shared/utils/token-util";
export default {
  setup() {
    let data = reactive({
      rooms: [],
      messages: [],
    });
    let form = reactive({
      room_id: null,
      message: "",
    });
    onCreated();
    //Methods
    function getMessages() {
      chatClient.getMessages(form.room_id).then((response) => {
        data.messages = response.data;
      });
    }
    function newMessage() {
      chatClient.newMessage(getForm()).then((response) => {
        form.message = "";
      });
    }
    //Watchers
    watch(
      () => form.room_id,
      (value, oldValue) => {
        if (oldValue) {
          disconnect(oldValue);
        }
        connect();
      }
    );
    //Commons
    function connect() {
      if (form.room_id) {
        getMessages();
        Echo.connector.options.auth.headers["Authorization"] =
          "Bearer " + TokenUtil.get();
        window.Echo.private("chat." + form.room_id).listen(".private-chat-event", (e) => {
          getMessages();
        });
      }
    }
    function disconnect(roomId) {
      window.Echo.leave("chat." + roomId);
    }
    function getForm() {
      return {
        chat_room_id: form.room_id,
        message: form.message,
      };
    }
    function onCreated() {
      getRooms();
    }
    function getRooms() {
      chatClient
        .getRooms()
        .then((response) => {
          data.rooms = response.data;
          form.room_id = data.rooms.length > 0 ? data.rooms[0].id : null;
        })
        .catch((error) => {
          console.log(error.response);
        });
    }
    return { newMessage, getMessages, ...toRefs(form), ...toRefs(data) };
  },
};
</script>

<style lang="scss">
.message {
  padding: 10px;
  height: 400px;
  overflow: auto;
  margin: 10px 0;
}
</style>
