<template>
    <div class="Main__Wrapper">
        <div class="NavBar__Container">
            <div class="Title">
                <CollectionIcon class="Icon" />
                <p>Products</p>
            </div>
            <div class="Search__Bar">
                <input type="text" class="Input" placeholder="Search product" />
                <SearchIcon class="Search__Icon" />
            </div>
            <div class="Options"></div>
        </div>
        <div class="Contents__Container">
            <div class="Heading">
                <div class="Left__Side">
                    <AdjustmentsIcon class="Icon" />
                    Filters
                </div>
                <div class="Right__Side">
                    <PrinterIcon class="Icon" />
                </div>
            </div>
            <div class="Table__Container">
                <table class="Table">
                    <thead class="Table__Head">
                        <tr class="Tr">
                            <td>Product name</td>
                            <td>Total Stock</td>
                            <td>Allocated</td>
                            <td>Available</td>
                            <td>Incoming</td>
                            <td>Cost</td>
                            <td>Price</td>
                        </tr>
                    </thead>
                    <tbody class="Table__Body">
                        <tr class="Tr">
                            <td>School Shoe</td>
                            <td>25</td>
                            <td>0</td>
                            <td>20</td>
                            <td>0</td>
                            <td>25,000</td>
                            <td>30,000</td>
                        </tr>
                    </tbody>
                </table>
            </div>
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
    SearchIcon,
    PrinterIcon,
} from "@heroicons/vue/outline";
export default {
    components: {
        SearchIcon,
        CollectionIcon,
        AdjustmentsIcon,
        PrinterIcon,
    },
};
</script>

<style lang="scss" scoped>
.Main__Wrapper {
    width: 100%;
    display: flex;
    flex-direction: column;
    .NavBar__Container {
        background-color: #fff;
        display: flex;
        align-items: center;
        width: 100%;
        padding: 10px;

        .Title {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 0 10px;
            border-right: 1px solid gray;
            margin-right: 25px;
            .Icon {
                height: 30px;
                object-fit: contain;
            }
        }

        .Search__Bar {
            display: flex;
            align-items: center;
            background-color: gray;
            border-radius: 5px;
            .Input {
                background: none;
                border: 0px;
                padding: 10px 20px;
                width: 200px;

                &:focus {
                    outline: none;
                    border: 0px;
                }
            }
            .Search__Icon {
                padding: 5px 20px;
                height: 30px;
                color: blue;
            }
        }
    }
    .Contents__Container {
        margin: 20px;
        background-color: #fff;
        border-radius: 3px;
        display: flex;
        flex-direction: column;
        .Heading {
            display: flex;
            justify-content: space-between;
            padding: 20px;
            border-bottom: 1px solid gray;
            .Left__Side {
                display: flex;
                gap: 10px;
                align-items: center;
                font-weight: 700;
                .Icon {
                    height: 20px;
                    object-fit: contain;
                }
            }
            .Right__Side {
                .Icon {
                    height: 30px;
                    object-fit: contain;
                    padding: 5px 20px;
                    border: 1px solid gray;
                    border-radius: 3px;
                }
            }
        }
        .Table__Container {
            .Table {
                width: 100%;

                .Table__Head {
                    font-weight: 500;
                    .Tr {
                    }
                }
                .Table__Body {
                    .Tr {
                        border-top: 1px solid gray;
                    }
                }
            }
        }
    }
}
</style>
