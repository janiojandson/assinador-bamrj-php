<?php
// Mantenha aqui no topo a sua lógica de Backend (Controllers)
// Exemplo:
// $uploadCtrl = new \App\Controllers\UploadController();
// $error = $uploadCtrl->handleUploadLegacy();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Inserir Empenho Legado - BAMRJ</title>
    <link rel="stylesheet" href="/static/css/style.css">
    <style>
        /* Ajustes finos para os inputs */
        .form-control { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-family: inherit; }
        .form-control:focus { border-color: #00447c; outline: none; box-shadow: 0 0 5px rgba(0,68,124,0.3); }
    </style>
</head>
<body style="background-color: #f0f2f5;">

    <header style="background: #00447c; color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
        <div style="display: flex; align-items: center; gap: 15px;">
            <img src="/static/img/brasao_bamrj.png" alt="BAMRJ" style="height: 40px;">
            <strong>BAMRJ | Inserir Empenho Legado (Arquivo Histórico)</strong>
        </div>
        <a href="/index" style="color: white; text-decoration: none; border: 1px solid white; padding: 6px 15px; border-radius: 4px; font-weight: bold; background: rgba(255,255,255,0.1); transition: 0.2s;">🏠 INÍCIO / DASHBOARD</a>
    </header>

    <main class="container" style="max-width: 850px; margin: 30px auto; background: white; padding: 35px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-top: 5px solid #e67e22;">
        
        <div style="background: #fff3cd; color: #856404; padding: 18px; border-radius: 4px; margin-bottom: 25px; border-left: 5px solid #ffc107; font-size: 0.95em; box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);">
            <strong style="font-size: 1.1em;">Atenção:</strong> Os documentos inseridos aqui não passarão pela cadeia de aprovação. Eles irão <strong>diretamente para o Arquivo Geral</strong> com o status de "Arquivado". Use esta ferramenta exclusivamente para digitalizar processos físicos antigos ou finalizados fora do sistema.
        </div>

        <form method="POST" enctype="multipart/form-data">
            
            <div style="display: flex; gap: 20px; margin-bottom: 18px;">
                <div class="form-group" style="flex: 2; margin: 0;">
                    <label style="font-weight: bold; color: #444; margin-bottom: 5px; display: block;">Protocolo Antigo (Opcional):</label>
                    <input type="text" name="protocolo_antigo" class="form-control" placeholder="Deixe em branco para o sistema gerar automaticamente">
                </div>
                <div class="form-group" style="flex: 1; margin: 0;">
                    <label style="font-weight: bold; color: #444; margin-bottom: 5px; display: block;">Ano de Referência:</label>
                    <select name="ano_referencia" class="form-control" required>
                        <?php for($i = date('Y'); $i >= 2010; $i--): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 18px;">
                <label style="font-weight: bold; color: #444; margin-bottom: 5px; display: block;">Nome do Favorecido / Assunto:</label>
                <input type="text" name="process_name" class="form-control" required placeholder="Ex: Aquisição de Material de Limpeza (Processo Físico 2023)">
            </div>

            <div style="display: flex; gap: 20px; margin-bottom: 25px;">
                <div class="form-group" style="flex: 1; margin: 0;">
                    <label style="font-weight: bold; color: #444; margin-bottom: 5px; display: block;">CPF / CNPJ:</label>
                    <input type="text" name="cpf_cnpj" class="form-control" placeholder="Apenas números">
                </div>
                <div class="form-group" style="flex: 1; margin: 0;">
                    <label style="font-weight: bold; color: #444; margin-bottom: 5px; display: block;">Nº SOLEMP ou NUP:</label>
                    <input type="text" name="solemp" class="form-control" placeholder="Número da SOLEMP ou NUP">
                </div>
            </div>

            <div class="form-group" style="background: #fdfaf6; padding: 20px; border-radius: 6px; border: 2px dashed #e67e22; margin-bottom: 30px; text-align: center;">
                <label style="font-weight: bold; color: #d35400; display: block; margin-bottom: 12px; font-size: 1.1em;">📄 Documento Final (PDF da Nota de Empenho assinada):</label>
                <input type="file" name="documento_final" accept=".pdf" required style="width: 100%; max-width: 400px; padding: 10px; background: white; border: 1px solid #ccc; border-radius: 4px; cursor: pointer;">
            </div>

            <button type="submit" style="width: 100%; height: 55px; background: #e67e22; color: white; font-weight: bold; font-size: 1.1em; border: none; border-radius: 4px; cursor: pointer; text-transform: uppercase; box-shadow: 0 4px 6px rgba(0,0,0,0.15); transition: background 0.2s;">
                Arquivar Documento Histórico
            </button>
        </form>
    </main>

    <script>
        // Efeito visual no botão de arquivamento
        const btnSubmit = document.querySelector('button[type="submit"]');
        btnSubmit.addEventListener('mouseover', () => btnSubmit.style.background = '#d35400');
        btnSubmit.addEventListener('mouseout', () => btnSubmit.style.background = '#e67e22');
    </script>
</body>
</html>