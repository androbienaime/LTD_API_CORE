import { configureStore, createAsyncThunk } from "@reduxjs/toolkit";
import {
    persistStore,
    persistReducer,
    FLUSH,
    REHYDRATE,
    PAUSE,
    PERSIST,
    PURGE,
    REGISTER
} from "redux-persist";
import storage from "redux-persist/lib/storage";
import appReducer from "./appSlicer";

const persistConfig = {
    key: "root",
    version:1,
    storage,
};

const persistedReducer = persistReducer(persistConfig, appReducer);



export const store = configureStore({
    reducer:{
        appReducer:persistedReducer
    },
    middleware: (getDefaultMiddleware) => 
        getDefaultMiddleware({
            serializableCheck : {
                ignoreActions : [FLUSH, REHYDRATE, PAUSE, PERSIST, PURGE, REGISTER],
            },
        }),
    
})

export let persistor = persistStore(store);