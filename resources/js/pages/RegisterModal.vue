<template>
  <div class="modal" v-if="registerModal">
    <div class="modal-content">
      <div class="form-wrap">
        <!-- Form -->
        <form class="register" @submit.prevent="register">
          <p class="login-register">
            Already have an account?
            <span class="router-link underline" @click="changeRegisterModal">
              Login
            </span>
          </p>
          <h2>Create Kwathu Account</h2>
          <div class="inputs">
            <!-- Name -->
            <div class="input">
              <input placeholder="Name" v-model="name" />
              <User class="icon" />
            </div>
            <!-- Email -->
            <div class="input">
              <input placeholder="Email" v-model="email" />
              <Email class="icon" />
            </div>
            <!-- Password -->
            <div class="input">
              <input
                type="password"
                placeholder="Password"
                v-model="password"
              />
              <Password class="icon" />
            </div>
            <!-- Password Confirmed -->
            <div class="input">
              <input
                type="password"
                placeholder="Confirm Password"
                v-model="password_confirmation"
              />
              <Password class="icon" />
            </div>
            <!-- Error -->
            <div class="error" v-show="error">{{ errorMessage }}</div>
            <!-- Sign Up -->
            <div>
              <button class="button" type="submit">Sign Up</button>
            </div>
          </div>
        </form>
        <!-- ---End Form--- -->
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters, mapActions } from "vuex";
import Email from "../assets/Icons/envelope-regular.svg";
import Password from "../assets/Icons/lock-alt-solid.svg";
import User from "../assets/Icons/user-alt-light.svg";
export default {
  props: ["modalMessage"],
  components: {
    Email,
    Password,
    User,
  },
  data() {
    return {
      email: null,
      password: null,
      password_confirmation: null,
      name: null,
    };
  },
  methods: {
    ...mapActions(["changeRegisterModal"]),
    register() {
      this.$store
        .dispatch("register", {
          name: this.name,
          email: this.email,
          password: this.password,
          password_confirmation: this.password_confirmation,
        })
        .catch((err) => {
          console.log(err);
        });
    },
  },
  computed: {
    ...mapGetters(["registerModal", "errorMessage", "error"]),
  },
};
</script>

<style lang="scss" scoped>
.modal {
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 101;
  position: absolute;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);

  .modal-content {
    display: flex;
    flex-direction: column;
    justify-content: center;
    border-radius: 8px;
    width: 500px;
    height: 85%;
    padding: 20px 30px;
    background-color: #fff;

    .form-wrap {
      overflow: hidden;
      display: flex;
      height: 100vh;
      justify-content: center;
      align-self: center;
      margin: 0 auto;
      width: 90%;
      @media (min-width: 900px) {
        width: 100%;
      }
      .login-register {
        margin-bottom: 32px;

        .router-link {
          color: #000;
        }
      }
      form {
        text-align: center;
        padding: 0 10px;
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        flex: 1;
        @media (min-width: 900px) {
          padding: 0 58px;
        }

        h2 {
          text-align: center;
          font-size: 32px;
          color: #303030;
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
              background-color: #f2f7f6;
              padding: 4px 4px 4px 30px;
              height: 50px;

              &:focus {
                outline: none;
              }
            }
            .icon {
              width: 12px;
              position: absolute;
              left: 6px;
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
          background-color: #303030;
          color: #fff;
          border-radius: 20px;
          bottom: none;
          text-transform: uppercase;

          &:focus {
            outline: none;
          }

          &:hover {
            background-color: rgba(48, 48, 48, 0.7);
          }
        }
      }
    }
  }
}
.register {
  h2 {
    max-width: 350px;
  }
}
</style>
