import React from "react";
import { RxSwitch } from "react-icons/rx";


const DarkMode = () => {
    const [theme, setTheme] = React.useState(
        localStorage.getItem("theme") ? localStorage.getItem("theme") : "light"
    );

    const element = document.documentElement;

    React.useEffect(()=> {
        if(theme === "dark"){
            element.classList.add("dark");
            localStorage.setItem("theme", "dark");
        }else{
            element.classList.remove("dark");
            localStorage.setItem("theme", "light");
        }
    }, [theme])
    return (
        <RxSwitch onClick={() => setTheme(theme === "light" ? "dark" : "light")} 
        className="text-[1.3em] text-white cursor-pointer"/>
    )
}

export default DarkMode;