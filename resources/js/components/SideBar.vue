<template>
    <div class="Main__Wrapper">
        <div class="Dzuka__Logo">PaMsika</div>
        <div class="Nav__Options">
            <router-link to="/dashboard">
                <div
                    :class="[
                        activeRoute == dashboard
                            ? Nav__Option__Active
                            : Nav__Option,
                    ]"
                >
                    <ColorSwatchIcon class="Icon" />
                    <p class="Title">Dashboard</p>
                </div></router-link
            >
            <router-link to="/inventory">
                <div class="Nav__Option">
                    <CollectionIcon class="Icon" />
                    <p class="Title">Categories/Products</p>
                </div>
            </router-link>
            <router-link to="/stock">
                <div class="Nav__Option">
                    <AdjustmentsIcon class="Icon" />
                    <p class="Title">Stock Control</p>
                </div>
            </router-link>
            <router-link to="/sales">
                <div class="Nav__Option">
                    <ShoppingBagIcon class="Icon" />
                    <p class="Title">Sales</p>
                </div>
            </router-link>
            <router-link to="/expenses">
                <div class="Nav__Option">
                    <CreditCardIcon class="Icon" />
                    <p class="Title">Expenses</p>
                </div></router-link
            >
            <router-link to="/transactions">
                <div class="Nav__Option">
                    <CreditCardIcon class="Icon" />
                    <p class="Title">Transactions</p>
                </div></router-link
            >
            <div v-if="userInfo.role == admin">
                <router-link to="/users">
                    <div class="Nav__Option">
                        <UsersIcon class="Icon" />
                        <p class="Title">Users</p>
                    </div>
                </router-link>
            </div>
        </div>
        <div class="Sign__Out">
            <LogoutIcon class="Icon" @click="logout" />
        </div>
    </div>
</template>

<script>
import {
    CollectionIcon,
    ColorSwatchIcon,
    AdjustmentsIcon,
    ShoppingBagIcon,
    ChartPieIcon,
    UsersIcon,
    CreditCardIcon,
    LogoutIcon,
} from "@heroicons/vue/outline";
import { mapActions, mapGetters } from "vuex";
export default {
    components: {
        CollectionIcon,
        ColorSwatchIcon,
        AdjustmentsIcon,
        ShoppingBagIcon,
        ChartPieIcon,
        UsersIcon,
        LogoutIcon,
        CreditCardIcon,
    },
    data() {
        return {
            activeRoute: null,
            Nav__Option: "Nav__Option",
            Nav__Option__Active: "Nav__Option",
            dashboard: "/",
            admin: "",
        };
    },
    computed: {
        ...mapGetters(["userInfo"]),
    },
    created() {
        this.getActiveRoute();
        this.setAdminVariable();
    },
    methods: {
        ...mapActions(["logout"]),
        getActiveRoute() {
            this.activeRoute = this.$route.path;
        },
        setAdminVariable() {
            this.admin = "admin";
        },
    },
};
</script>

<style lang="scss" scoped>
.Main__Wrapper {
    position: relative;
    height: 100vh;
    background-color: rgb(30 41 59);
    flex: 0.2;
    min-width: 250px;
    padding: 10px 0;
    display: flex;
    flex-direction: column;

    .Dzuka__Logo {
        padding: 25px;
        color: #fff;
        font-weight: 900;
        font-size: 30px;
        cursor: pointer;
    }
    .Nav__Options {
        display: flex;
        flex-direction: column;
        margin-top: 10px;

        .Nav__Option {
            display: flex;
            gap: 10px;
            align-items: center;
            padding: 15px 25px;
            color: rgb(100 116 139);
            cursor: pointer;

            &:hover {
                background: rgb(15 23 42);
                color: rgb(29 78 216);
            }

            .Icon {
                height: 25px;
            }
            .Title {
                color: rgb(203 213 225);

                &:hover {
                    color: #fff;
                }
            }
        }
        .Nav__Option__Active {
            display: flex;
            gap: 10px;
            align-items: center;
            padding: 15px 25px;
            background: rgb(15 23 42);
            color: rgb(29 78 216);
            cursor: pointer;

            .Icon {
                height: 25px;
            }
            .Title {
                color: rgb(203 213 225);
                &:hover {
                    color: #fff;
                }
            }
        }
    }
    .Sign__Out {
        position: absolute;
        display: grid;
        place-items: center;
        background: rgb(15 23 42);
        padding: 30px;
        bottom: 0;
        width: 100%;
        .Icon {
            object-fit: contain;
            height: 25px;
            color: rgb(29 78 216);
            cursor: pointer;
        }
    }
}
</style>
