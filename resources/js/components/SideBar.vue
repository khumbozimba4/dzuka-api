<template>
    <div class="Main__Wrapper">
        <div class="Dzuka__Logo">PaMsika</div>
        <div class="User__Profile" @click="openUserProfileModal">
            <UserCircleIcon class="User__Icon" />
            <p>{{ userInfo.name }}</p>
        </div>

        <div class="Nav__Options">
            <router-link to="/dashboard">
                <div
                    :class="[
                        activeRoute == 'dashboard'
                            ? 'Nav__Link__Active'
                            : 'Nav__Link',
                    ]"
                >
                    <ColorSwatchIcon class="Icon" />
                    <p class="Title">Dashboard</p>
                </div></router-link
            >
            <router-link to="/inventory">
                <div
                    :class="[
                        activeRoute == 'inventory'
                            ? 'Nav__Link__Active'
                            : 'Nav__Link',
                    ]"
                >
                    <CollectionIcon class="Icon" />
                    <p class="Title">Categories/Products</p>
                </div>
            </router-link>
            <router-link to="/stock">
                <div
                    :class="[
                        activeRoute == 'stock'
                            ? 'Nav__Link__Active'
                            : 'Nav__Link',
                    ]"
                >
                    <AdjustmentsIcon class="Icon" />
                    <p class="Title">Stock</p>
                </div>
            </router-link>
            <router-link to="/sales">
                <div
                    :class="[
                        activeRoute == 'sales'
                            ? 'Nav__Link__Active'
                            : 'Nav__Link',
                    ]"
                >
                    <ShoppingBagIcon class="Icon" />
                    <p class="Title">Sales</p>
                </div>
            </router-link>
            <router-link to="/expenses">
                <div
                    :class="[
                        activeRoute == 'expenses'
                            ? 'Nav__Link__Active'
                            : 'Nav__Link',
                    ]"
                >
                    <CreditCardIcon class="Icon" />
                    <p class="Title">Expenses</p>
                </div></router-link
            >
            <router-link to="/transactions">
                <div
                    :class="[
                        activeRoute == 'transactions'
                            ? 'Nav__Link__Active'
                            : 'Nav__Link',
                    ]"
                >
                    <CreditCardIcon class="Icon" />
                    <p class="Title">Audit Trail</p>
                </div></router-link
            >
            <div v-if="userInfo.role == admin">
                <router-link to="/users">
                    <div
                        :class="[
                            activeRoute == 'users'
                                ? 'Nav__Link__Active'
                                : 'Nav__Link',
                        ]"
                    >
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
    UserCircleIcon,
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
        UserCircleIcon,
        LogoutIcon,
        CreditCardIcon,
    },
    data() {
        return {};
    },
    computed: {
        ...mapGetters(["userInfo", "user"]),
        activeRoute() {
            return this.$route.name;
        },
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
        openUserProfileModal() {
            this.$store.commit("setUserProfileModal", true);
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
        padding: 25px 25px 5px 25px;
        color: #fff;
        font-weight: 900;
        font-size: 30px;
        cursor: pointer;
    }
    .User__Profile {
        display: flex;
        gap: 15px;
        width: 60%;
        color: rgb(196, 196, 196);
        padding: 7px;
        border: 1px solid gray;
        border-radius: 3px;
        margin-left: 35px;
        cursor: pointer;
        .User__Icon {
            width: 25px;
            object-fit: contain;
            color: #fff;
        }
    }
    .Nav__Options {
        display: flex;
        flex-direction: column;
        margin-top: 10px;

        .Nav__Link {
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
        .Nav__Link__Active {
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
