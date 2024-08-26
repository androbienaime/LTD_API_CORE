import React, { createContext, useContext, useState } from 'react';

// Créer le contexte
const ModalContext = createContext();

// Fournisseur de contexte
export const ModalProvider = ({ children }) => {
  const [isOpen, setIsOpen] = useState(false);

  const openModal = () => setIsOpen(true);
  const closeModal = () => setIsOpen(false);

  return (
    <ModalContext.Provider value={{ isOpen, openModal, closeModal }}>
      {children}
    </ModalContext.Provider>
  );
};

// Custom hook pour utiliser le contexte
export const useModal = () => {
    const context = useContext(ModalContext);
    
    if(context === undefined){
      throw new Error("useModal must be used on a ModalProvider");
    }

    return context;
}