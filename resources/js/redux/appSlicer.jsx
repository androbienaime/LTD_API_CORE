import { createSlice } from "@reduxjs/toolkit";
import { toast } from "react-toastify";

const initialState = {
    userInfos : [],
    products : [],
    checkedBrands : [],
    checkedCategorys : []
};

export const appSlicer = createSlice({
    name : "app",
    initialState,
    reducers : {
        addToCart : (state, action) =>{
            const item = state.products.find(
                (item) => item.id === action.payload.id
            );

            if(item){
                item.quantity += action.payload.quantity;
            }else{
                state.products.push(action.payload);
            }

            toast.success("Product added to cart");
        },
        increaseQuantity : (state, action) =>{
            const item = state.products.find(
                (item) => item.id === action.payload.id
            );
            console.log(item);
            
            if(item){
                item.quantity++;
            }
        },
        decreaseQuantity : (state, action) =>{
            const item = state.products.find(
                (item) => item.id === action.payload.id
            );
            
            if(item.quantity == 1 ){
                item.quantity = 1;
            }else{
                item.quantity--;
            }
        },
        deleteItem: (state, action) => {
            state.products = state.products.filter(
              (item) => item.id !== action.payload
            );
            // Dispatch a success toast
            toast.error("Product removed from cart");
          },
          resetCart: (state) => {
            state.products = [];
            // Dispatch a success toast
          },
      
          toggleBrand: (state, action) => {
            const brand = action.payload;
            const isBrandChecked = state.checkedBrands.some(
              (b) => b._id === brand._id
            );
      
            if (isBrandChecked) {
              state.checkedBrands = state.checkedBrands.filter(
                (b) => b._id !== brand._id
              );
            } else {
              state.checkedBrands.push(brand);
            }
          },
      
          toggleCategory: (state, action) => {
            const category = action.payload;
            const isCategoryChecked = state.checkedCategorys.some(
              (b) => b._id === category._id
            );
      
            if (isCategoryChecked) {
              state.checkedCategorys = state.checkedCategorys.filter(
                (b) => b._id !== category._id
              );
            } else {
              state.checkedCategorys.push(category);
            }
          },
    }
});

export const {
    addToCart,
    increaseQuantity,
    decreaseQuantity,
    deleteItem,
    resetCart,
    toggleBrand,                                                                                      
    toggleCategory,
  } = appSlicer.actions;

  export default appSlicer.reducer;