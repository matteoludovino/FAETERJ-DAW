const express = require('express');
const bodyParser = require('body-parser');
const fs = require('fs');
const path = require('path');

const app = express();
const PORT = 3000;

app.use(bodyParser.urlencoded({ extended: true }));
app.use(bodyParser.json());
app.use(express.static('public'));

app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'views', 'index.html'));
});

app.get('/cadastrar_usuario', (req, res) => {
    res.sendFile(path.join(__dirname, 'views', 'cadastrar_usuario.html'));
});

app.get('/listar_usuarios', (req, res) => {
    res.sendFile(path.join(__dirname, 'views', 'listar_usuarios.html'));
});

app.get('/editar_usuario', (req, res) => {
    res.sendFile(path.join(__dirname, 'views', 'editar_usuario.html'));
});

app.post('/atualizar_usuario', (req, res) => {
    const { email_antigo, nome, email, senha, nivel } = req.body;
    
    const usuariosPath = path.join(__dirname, 'data', 'usuarios.txt');
    
    if (fs.existsSync(usuariosPath)) {
        const linhas = fs.readFileSync(usuariosPath, 'utf8').split('\n');
        let conteudoFinal = '';
        const cabecalho = linhas[0];
        conteudoFinal += cabecalho + '\n';
        
        for (let i = 1; i < linhas.length; i++) {
            const linhaLimpa = linhas[i].trim();
            if (linhaLimpa === '') continue;
            
            const dados = linhaLimpa.split(';');
            if (dados[1] === email_antigo) {
                conteudoFinal += `${nome};${email};${senha};${nivel}\n`;
            } else {
                conteudoFinal += linhaLimpa + '\n';
            }
        }
        
        fs.writeFileSync(usuariosPath, conteudoFinal);
    }
    
    res.redirect('/listar_usuarios');
});

app.get('/excluir_usuario', (req, res) => {
    const emailExcluir = req.query.email;
    
    if (emailExcluir) {
        const usuariosPath = path.join(__dirname, 'data', 'usuarios.txt');
        
        if (fs.existsSync(usuariosPath)) {
            const linhas = fs.readFileSync(usuariosPath, 'utf8').split('\n');
            const cabecalho = linhas[0];
            const novasLinhas = [cabecalho];
            
            for (let i = 1; i < linhas.length; i++) {
                const linha = linhas[i];
                if (linha.trim() === '') continue;
                
                const dados = linha.split(';');
                if (dados[1] !== emailExcluir) {
                    novasLinhas.push(linha);
                }
            }
            
            fs.writeFileSync(usuariosPath, novasLinhas.join('\n') + '\n');
        }
    }
    
    res.redirect('/listar_usuarios');
});

app.post('/salvar_usuario', (req, res) => {
    const { nome, email, senha, nivel } = req.body;
    const linha = `${nome};${email};${senha};${nivel}\n`;
    
    const usuariosPath = path.join(__dirname, 'data', 'usuarios.txt');
    
    if (!fs.existsSync(usuariosPath)) {
        fs.writeFileSync(usuariosPath, 'nome;email;senha;nivel\n');
    }
    
    fs.appendFileSync(usuariosPath, linha);
    res.redirect('/listar_usuarios');
});

app.get('/cadastrar_pergunta', (req, res) => {
    res.sendFile(path.join(__dirname, 'views', 'cadastrar_pergunta.html'));
});

app.get('/listar_perguntas', (req, res) => {
    res.sendFile(path.join(__dirname, 'views', 'listar_perguntas.html'));
});

app.get('/editar_pergunta', (req, res) => {
    res.sendFile(path.join(__dirname, 'views', 'editar_pergunta.html'));
});

app.get('/visualizar_pergunta', (req, res) => {
    res.sendFile(path.join(__dirname, 'views', 'visualizar_pergunta.html'));
});

app.post('/atualizar_pergunta', (req, res) => {
    const { pergunta_antiga, tipo, pergunta, opcao1, opcao2, opcao3, opcao4, resposta_correta } = req.body;
    
    const perguntasPath = path.join(__dirname, 'data', 'perguntas.txt');
    
    if (fs.existsSync(perguntasPath)) {
        const linhas = fs.readFileSync(perguntasPath, 'utf8').split('\n');
        let conteudoFinal = '';
        
        for (const linha of linhas) {
            const linhaLimpa = linha.trim();
            if (linhaLimpa === '') continue;
            
            const dados = linhaLimpa.split(';');
            if (dados[1] === pergunta_antiga) {
                if (tipo === 'multipla') {
                    conteudoFinal += `${tipo};${pergunta};${opcao1};${opcao2};${opcao3};${opcao4};${resposta_correta}\n`;
                } else {
                    conteudoFinal += `${tipo};${pergunta};;\n`;
                }
            } else {
                conteudoFinal += linhaLimpa + '\n';
            }
        }
        
        fs.writeFileSync(perguntasPath, conteudoFinal);
    }
    
    res.redirect('/listar_perguntas');
});

app.get('/excluir_pergunta', (req, res) => {
    const perguntaExcluir = req.query.pergunta;
    
    if (perguntaExcluir) {
        const perguntasPath = path.join(__dirname, 'data', 'perguntas.txt');
        
        if (fs.existsSync(perguntasPath)) {
            const linhas = fs.readFileSync(perguntasPath, 'utf8').split('\n');
            const novasLinhas = [];
            
            for (const linha of linhas) {
                const linhaLimpa = linha.trim();
                if (linhaLimpa === '') continue;
                
                const dados = linhaLimpa.split(';');
                if (dados[1] !== perguntaExcluir) {
                    novasLinhas.push(linha);
                }
            }
            
            fs.writeFileSync(perguntasPath, novasLinhas.join('\n') + '\n');
        }
    }
    
    res.redirect('/listar_perguntas');
});

app.post('/salvar_pergunta', (req, res) => {
    const { tipo, pergunta, opcao1, opcao2, opcao3, opcao4, resposta_correta } = req.body;
    let linha;
    
    if (tipo === 'multipla') {
        linha = `${tipo};${pergunta};${opcao1};${opcao2};${opcao3};${opcao4};${resposta_correta}\n`;
    } else {
        linha = `${tipo};${pergunta};;\n`;
    }
    
    const perguntasPath = path.join(__dirname, 'data', 'perguntas.txt');
    fs.appendFileSync(perguntasPath, linha);
    res.redirect('/listar_perguntas');
});

app.get('/api/usuarios', (req, res) => {
    const usuariosPath = path.join(__dirname, 'data', 'usuarios.txt');
    const usuarios = [];
    
    if (fs.existsSync(usuariosPath)) {
        const linhas = fs.readFileSync(usuariosPath, 'utf8').split('\n');
        for (let i = 1; i < linhas.length; i++) {
            const linha = linhas[i].trim();
            if (linha) {
                const dados = linha.split(';');
                if (dados.length >= 4) {
                    usuarios.push({
                        nome: dados[0],
                        email: dados[1],
                        senha: dados[2],
                        nivel: dados[3]
                    });
                }
            }
        }
    }
    
    res.json(usuarios);
});

app.get('/api/usuario/:email', (req, res) => {
    const emailAlvo = req.params.email;
    const usuariosPath = path.join(__dirname, 'data', 'usuarios.txt');
    let usuarioEncontrado = null;
    
    if (fs.existsSync(usuariosPath)) {
        const linhas = fs.readFileSync(usuariosPath, 'utf8').split('\n');
        for (let i = 1; i < linhas.length; i++) {
            const linha = linhas[i].trim();
            if (linha) {
                const dados = linha.split(';');
                if (dados[1] === emailAlvo) {
                    usuarioEncontrado = dados;
                    break;
                }
            }
        }
    }
    
    res.json(usuarioEncontrado);
});

app.get('/api/perguntas', (req, res) => {
    const perguntasPath = path.join(__dirname, 'data', 'perguntas.txt');
    const perguntas = [];
    
    if (fs.existsSync(perguntasPath)) {
        const linhas = fs.readFileSync(perguntasPath, 'utf8').split('\n');
        for (const linha of linhas) {
            const linhaLimpa = linha.trim();
            if (linhaLimpa) {
                const dados = linhaLimpa.split(';');
                if (dados.length >= 2) {
                    perguntas.push({
                        tipo: dados[0],
                        pergunta: dados[1],
                        dados: dados
                    });
                }
            }
        }
    }
    
    res.json(perguntas);
});

app.get('/api/pergunta/:pergunta', (req, res) => {
    const perguntaAlvo = req.params.pergunta;
    const perguntasPath = path.join(__dirname, 'data', 'perguntas.txt');
    let perguntaDados = null;
    
    if (fs.existsSync(perguntasPath)) {
        const linhas = fs.readFileSync(perguntasPath, 'utf8').split('\n');
        for (const linha of linhas) {
            const linhaLimpa = linha.trim();
            if (linhaLimpa) {
                const dados = linhaLimpa.split(';');
                if (dados[1] === perguntaAlvo) {
                    perguntaDados = dados;
                    break;
                }
            }
        }
    }
    
    res.json(perguntaDados);
});

if (!fs.existsSync(path.join(__dirname, 'data'))) {
    fs.mkdirSync(path.join(__dirname, 'data'));
}
if (!fs.existsSync(path.join(__dirname, 'views'))) {
    fs.mkdirSync(path.join(__dirname, 'views'));
}

app.listen(PORT, () => {
    console.log(`Servidor rodando em http://localhost:${PORT}`);
});