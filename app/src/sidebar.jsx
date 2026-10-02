import { useEffect, useState } from 'react';
import './sidebar.css';

export default function Sidebar({ onSair }) {
  // Estado para controlar se o menu está aberto ou fechado
  const [isOpen, setIsOpen] = useState(false);

  const toggleMenu = () => {
    setIsOpen(!isOpen);
  };

  const closeMenu = () => setIsOpen(false);

  // Fecha o menu com a tecla Esc
  useEffect(() => {
    if (!isOpen) return undefined;
    const onKeyDown = (event) => {
      if (event.key === 'Escape') setIsOpen(false);
    };
    window.addEventListener('keydown', onKeyDown);
    return () => window.removeEventListener('keydown', onKeyDown);
  }, [isOpen]);

  return (
    <div className="app-container">

      <button
        className={`menu-btn ${isOpen ? 'open' : ''}`}
        type="button"
        onClick={toggleMenu}
        aria-label={isOpen ? 'Fechar menu' : 'Abrir menu'}
        aria-expanded={isOpen}
        aria-controls="menu-principal"
      >
        <span className="menu-btn-barra" />
        <span className="menu-btn-barra" />
        <span className="menu-btn-barra" />
      </button>

      <nav id="menu-principal" className={`sidebar ${isOpen ? 'open' : ''}`} inert={!isOpen}>
        <ul>
          <li><a href="#home" onClick={closeMenu}>Meu perfil</a></li>
          <li><a href="#services" onClick={closeMenu}>Mudar Local</a></li>
          <li><a href="#portfolio" onClick={closeMenu}>Col. Seletiva</a></li>
          <li><a href="#contact" onClick={closeMenu}>Configurações</a></li>
          <li>
            <a
              href="#contact"
              onClick={(event) => {
                event.preventDefault();
                closeMenu();
                onSair?.();
              }}
            >
              Sair do Perfil
            </a>
          </li>
        </ul>
      </nav>

      {isOpen && <div className="overlay" onClick={closeMenu}></div>}

    </div>
  );
}
