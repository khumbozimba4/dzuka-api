<template>
    <div
        class="Main__Wrap"
        ref="login"
        :style="{
            backgroundImage: `url(${Image})`,
            backgroundSize: 'cover',
        }"
    >
        <div class="Modal__Content">
            <div class="Main__Content">
                <div class="Logo__Content">
                    <img :src="'/images/login-design.png'" alt="" />
                </div>
                <div class="form-wrap">
                    <form class="login" @submit.prevent="login">
                        <h2>Login</h2>
                        <div class="inputs">
                            <!-- Email -->
                            <div class="input">
                                <input
                                    placeholder="Email"
                                    type="email"
                                    v-model="email"
                                />
                                <MailIcon class="icon" />
                            </div>
                            <!-- Password -->
                            <div class="input">
                                <input
                                    type="password"
                                    placeholder="Password"
                                    v-model="password"
                                />
                                <LockClosedIcon class="icon" />
                            </div>
                            <!-- Error -->
                            <div class="error" v-show="error">
                                {{ errorMessage }}
                            </div>
                            <!-- Forgot your password -->
                            <router-link class="forgot-password" to="#">
                                Forgot your password?
                            </router-link>
                            <!-- Sign in -->
                            <div>
                                <button class="button">Sign in</button>
                            </div>
                        </div>
                    </form>
                    <!-- ---End Form--- -->
                    <!-- background -->
                    <Loading v-if="isLoading" />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { MailIcon, LockClosedIcon } from "@heroicons/vue/outline";
import Loading from "../components/Loading.vue";
import { mapGetters, mapActions } from "vuex";
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
            Image: "'/images/bg.jpg'",
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
    justify-content: center;
    align-items: center;
    z-index: 101;
    position: absolute;
    width: 100%;
    height: 100%;

    .Modal__Content {
        display: flex;
        justify-content: center;
        border-radius: 8px;
        width: 70%;
        height: 80%;
        background-color: rgba(30, 41, 59, 0.8);

        .Main__Content {
            display: flex;
            align-items: center;
            gap: 30px;
            .Logo__Content {
                display: flex;
                flex-direction: column;
                img {
                    width: 300px;
                    object-fit: contain;
                }
            }
            .form-wrap {
                padding: 20px 0;
                overflow: hidden;
                display: flex;
                width: 400px;
                border-radius: 5px;
                background-color: rgba(118, 134, 160, 0.8);
                .login-register {
                    margin-bottom: 32px;

                    .router-link {
                        color: #000;
                    }
                }
                form {
                    text-align: center;
                    position: relative;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    align-items: center;
                    flex: 1;
                    @media (min-width: 900px) {
                        padding: 0 58px;
                    }
                    p {
                        .router-link {
                            cursor: pointer;
                        }
                    }

                    h2 {
                        text-align: center;
                        font-size: 32px;
                        color: #fff;
                        margin-bottom: 40px;
                        @media (min-width: 900px) {
                            font-size: 40px;
                        }
                    }

                    .inputs {
                        width: 100%;
                        max-width: 350px;

                        .input {
                            position: relative;
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            margin-bottom: 8px;

                            input {
                                width: 100%;
                                border: none;
                                padding: 4px 4px 4px 30px;
                                height: 50px;
                                border-radius: 2px;

                                &:focus {
                                    outline: none;
                                }
                            }
                            .icon {
                                width: 15px;
                                object-fit: contain;
                                position: absolute;
                                left: 6px;
                            }
                            .error {
                                color: rgb(182, 86, 86);
                            }
                        }
                    }

                    .forgot-password {
                        text-decoration: none;
                        color: #000;
                        cursor: pointer;
                        font-size: 14px;
                        margin: 16px 0 32px;
                        border-bottom: 1px solid transparent;
                        transition: 0.5s ease all;

                        &:hover {
                            border-color: #303030;
                        }
                    }

                    .button {
                        transition: 500ms ease all;
                        cursor: pointer;
                        margin-top: 24px;
                        padding: 8px 12px;
                        background-color: rgba(30, 41, 59, 0.8);
                        color: #fff;
                        border-radius: 10px;
                        bottom: none;

                        &:focus {
                            outline: none;
                        }

                        &:hover {
                            background-color: rgba(19, 28, 43, 0.8);
                        }
                    }
                }
            }
        }
    }
}
</style>
