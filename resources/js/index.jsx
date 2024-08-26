import React from "react";
import App from "./components/App";
import ReactDOM from 'react-dom/client';
import { PersistGate } from "redux-persist/integration/react";
import {Provider} from "react-redux";
import { store, persistor } from "./redux/store";

if (document.getElementById('app')) {
    const Index = ReactDOM.createRoot(document.getElementById("app"));

    Index.render(
        <React.StrictMode>
            <Provider store={store}>
                <PersistGate loading={null} persistor={persistor}>
                    <App/>
                </PersistGate>
            </Provider>
        </React.StrictMode>
    )
}