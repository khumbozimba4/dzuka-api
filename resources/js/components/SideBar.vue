<template>
    <div class="Main__Wrapper">
        <div class="Dzuka__Logo">DZUKA SALES</div>
        <div class="Nav__Options">
            <div class="Nav__Option">
                <ColorSwatchIcon class="Icon" />
                <p class="Title">Dashboard</p>
            </div>
            <div class="Nav__Option">
                <CollectionIcon class="Icon" />
                <p class="Title">Inventory</p>
            </div>
            <div class="Nav__Option">
                <AdjustmentsIcon class="Icon" />
                <p class="Title">Stock Control</p>
            </div>
            <div class="Nav__Option">
                <ShoppingBagIcon class="Icon" />
                <p class="Title">Sales</p>
            </div>
            <div class="Nav__Option">
                <ChartPieIcon class="Icon" />
                <p class="Title">Analytics</p>
            </div>
            <div class="Nav__Option">
                <UsersIcon class="Icon" />
                <p class="Title">Users</p>
            </div>
        </div>
        <div class="Sign__Out">
            <LogoutIcon class="Icon" />
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
    LogoutIcon,
} from "@heroicons/vue/outline";
export default {
    components: {
        CollectionIcon,
        ColorSwatchIcon,
        AdjustmentsIcon,
        ShoppingBagIcon,
        ChartPieIcon,
        UsersIcon,
        LogoutIcon,
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
        font-weight: 800;
        font-size: 20px;
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
