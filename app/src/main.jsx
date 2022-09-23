import "./App.css"

import React from "react"
import ReactDOM from "react-dom/client"
import App from "./App"
import { Toaster } from "@uvodohq/planum"

ReactDOM.createRoot(document.getElementById("root")).render(
  <React.StrictMode>
    <App />
    <Toaster position={"top-center"}/>
  </React.StrictMode>
)
