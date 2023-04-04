<template>
    <div class="Main__Wrap">
        <!--        @Cover Image-->
        <div class="Cover__Image"
             :style="{
                backgroundImage: `url(${Image})`,
                backgroundSize: 'cover',
            }"
        ></div>
        <!--        @Form-->
        <form @submit.prevent="login">
            <img :src="'/images/6to6-black.png'" style="width: 150px; height: 150px; object-fit: contain">
            <h2>Login</h2>
            <!-- Email -->
            <div class="input__container">
                <MailIcon class="icon"/>
                <input
                    placeholder="Email"
                    type="email"
                    v-model="email"
                />
            </div>
            <!-- Password -->
            <div class="input__container">
                <LockClosedIcon class="icon"/>
                <input
                    type="password"
                    placeholder="Password"
                    v-model="password"
                />
            </div>
            <!-- Error -->
            <div class="error text-red-500" v-show="error">
                {{ errorMessage }}
            </div>
            <div>
                <button class="button">Sign in</button>
            </div>
        </form>
    </div>
</template>

<script>
import {MailIcon, LockClosedIcon} from "@heroicons/vue/outline";
import Loading from "../components/Loading.vue";
import {mapGetters, mapActions} from "vuex";

export default {
    props: ["modalMessage"],
    components: {
        Loading,
        MailIcon,
        LockClosedIcon,
    },
    data() {
        return {
            email: null,
            password: null,
            Image: "'/images/bg3.jpg'",
        };
    },
    methods: {
        ...mapActions(["changeRegisterModal", "closeLoginModal"]),
        login() {
            this.$store
                .dispatch("login", {
                    email: this.email,
                    password: this.password,
                })
                .catch((err) => {
                    console.log(err);
                });
        },
    },
    computed: {
        ...mapGetters(["registerModal", "errorMessage", "error", "isLoading"]),
    },
};
</script>

<style lang="scss" scoped>
.Main__Wrap {
    display: flex;
    width: 100vw;

    .Cover__Image {
        width: 70%;
        height: 100vh;
    }

    form {
        background: #fff;
        width: 30%;
        justify-items: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding-top: 5%;

        h2 {
            font-size: 25px;
            color: rgba(30, 41, 59, 0.9);
            margin-bottom: 40px;
            font-weight: 500;
        }

        .input__container {
            padding: 15px;
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            width: 80%;
            border-radius: 5px;
            background: rgb(232,240,254);
            border: 1px solid rgb(229 231 235);

            .icon {
                width: 20px;
                height: 20px;
                object-fit: contain;
                margin: 10px;
            }

            input {
                flex: 1;
                border-radius: 2px;
                border: none;
                background: rgb(232,240,254);
            }
        }

        .button {
            transition: 500ms ease all;
            cursor: pointer;
            margin-top: 24px;
            padding: 15px;
            background-color: rgba(30, 41, 59, 0.8);
            color: #fff;
            border-radius: 10px;
            font-weight: bold;
            text-align: center;
            width: 100px;

            &:focus {
                outline: none;
            }

            &:hover {
                background-color: rgba(19, 28, 43, 0.8);
            }
        }
    }
}
</style>
