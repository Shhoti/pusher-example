<template>
    <div class="row">
            <div class="col-md-8">
                <ul>
                    <li v-for="task in tasks" v-text="task"></li>
                </ul>

            <input type="text" v-model="newTask"  @blur="addTask" @keydown="tapParticipants">
            <div v-if="activePeer" v-text="activePeer.name + ' is typing...' "></div>
        </div>
        <div class="col-md-4">
            <h2>Active Participants</h2>
            <ul>
                <li v-for="participant in participants" v-text="participant.name"></li>
            </ul>
        </div>
    </div>

</template>

<script>
    export default {
        props: ['project'], //accept the project prop 
        data() {
            return {
                tasks : [],
                newTask: '',
                activePeer: false,
                typingTimer: false,
                participants: [],
                
            }
        },
        computed:{
            channel()
            {
                return window.Echo.join('tasks' + this.project.id)
            }
        },  
        methods: {
            flashPeer(e){
                this.activePeer = e;

                if(this.typingTimer) clearTimeout(this.typingTimer);
                
                this.typingTimer = setTimeout(() => {
                    this.activePeer = false;
                }, 3000);
            },
            tapParticipants(){
                this.channel
                    .whisper('typing',{
                        name: window.App.user.name
                    });
            },

            addTask() {
                axios.post(`/api/projects/${this.project.id}/tasks`, {body: this.newTask});

                this.activePeer = false;
                this.tasks.push(this.newTask);
                this.newTask = '';
            }
        },
        created() {
            axios.get('/api/projects/' + this.project.id).then(response => (this.tasks = response.data));            

            //listen for when tasks are created with Pusher
            this.channel
            .here(users => {
                this.participants = users;
            })
            .joining(user => {
                this.participants.push(user);
            })
            .leaving(user => {
               this.participants.splice(this.participants.indexOf(user),1); 
            })
            .listen('TaskCreated', e => {
                this.tasks.push(e.task.body);
            })
            .listenForWhisper('typing', this.flashPeer)

        }
    }
</script>
