#!/bin/bash

REPO_URL="https://github.com/edsuuu/template-laravel.git"
RED='\033[0;31m'
GREEN='\033[0;32m'
NC='\033[0m'

spinner() {
    local pid=$1
    local delay=0.1
    local spinstr='|/-\'
    while [ "$(ps a | awk '{print $1}' | grep $pid)" ]; do
        local temp=${spinstr#?}
        printf " [%c]  " "$spinstr"
        local spinstr=$temp${spinstr%"$temp"}
        sleep $delay
        printf "\b\b\b\b\b\b"
    done
    printf "    \b\b\b\b"
}

read -p "Digite o nome do novo projeto: " PROJECT_NAME

if [ -z "$PROJECT_NAME" ]; then
    echo -e "${RED}Erro: Nome do projeto é obrigatório.${NC}"
    exit 1
fi

DEFAULT_DB_NAME=$(echo "$PROJECT_NAME" | tr '[:upper:]' '[:lower:]' | tr '-' '_')
read -p "Digite o nome do banco de dados [$DEFAULT_DB_NAME]: " DB_NAME
DB_NAME=${DB_NAME:-$DEFAULT_DB_NAME}

for cmd in git php composer; do
    if ! command -v $cmd &> /dev/null; then
        echo -e "${RED}Você não tem o comando $cmd instalado. Instale e por favor tente rodar o instalador novamente.${NC}"
        exit 1
    fi
done

PKG_MANAGER="npm"
if command -v pnpm &> /dev/null; then
    PKG_MANAGER="pnpm"
fi

echo -e "Clonando repositório..."
git clone $REPO_URL $PROJECT_NAME &> /dev/null || { echo -e "${RED}Falha ao clonar o repositório.${NC}"; exit 1; }

cd $PROJECT_NAME

# Utilizando comando do composer para inicializar o .env
composer run post-root-package-install &> /dev/null

sedi() {
    if [[ "$OSTYPE" == "darwin"* ]]; then
        sed -i "" "$@"
    else
        sed -i "$@"
    fi
}

# Atualizando .env
sedi "s/APP_NAME=.*/APP_NAME=\"$PROJECT_NAME\"/g" .env
sedi "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/g" .env

# Utilizando composer config para alterar o nome do projeto
composer config name "edsuuu/$PROJECT_NAME" &> /dev/null

if [ "$PKG_MANAGER" = "pnpm" ]; then
    sedi "s/npm install/pnpm install/g" composer.json
    sedi "s/npm run build/pnpm run build/g" composer.json
fi

echo -n "Executando setup do projeto (isso pode levar alguns minutos)..."
# Removendo migrate do setup via sed para permitir escolha no final
sedi "s/\"@php artisan migrate --force\",//g" composer.json

composer setup &> /dev/null &
SETUP_PID=$!
spinner $SETUP_PID
wait $SETUP_PID
SETUP_EXIT_CODE=$?

if [ $SETUP_EXIT_CODE -eq 0 ]; then
    echo -e "\n${GREEN}Setup concluído!${NC}"
    
    read -p "Deseja rodar as migrations e seeders agora? (y/n): " RUN_MIGRATIONS
    if [[ "$RUN_MIGRATIONS" =~ ^[Yy]$ ]]; then
        echo -e "Rodando migrations e seeders..."
        php artisan migrate:fresh --seed
    fi

    echo -e "Inicializando novo repositório Git..."
    rm -rf .git
    git init &> /dev/null
    git add . &> /dev/null
    git commit -m 'add first boilerplate' &> /dev/null
    
    echo -e "${GREEN}Projeto ${PROJECT_NAME} pronto para uso.${NC}"
    echo -e "Acesse: cd ${PROJECT_NAME} && composer dev"
else
    echo -e "\n${RED}Falha no setup do projeto.${NC}"
    exit 1
fi
