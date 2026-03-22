<?php
$adminCtrl = new \App\Controllers\AdminController();
$adminCtrl->handleCreate();
$adminCtrl->handleEdit();
$users = $adminCtrl->listUsers();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gestão de Utilizadores - BAMRJ</title>
    <link rel="stylesheet" href="/static/css/style.css">
</head>
<body style="background-color: #f0f2f5;">
    <header style="background: #00447c; color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
        <div><strong>BAMRJ</strong> | Administração de Sistema</div>
        <a href="/index" style="color: white; text-decoration: none; border: 1px solid white; padding: 5px 10px; border-radius: 4px;">Voltar ao Dashboard</a>
    </header>

    <main class="container" style="padding: 20px; max-width: 1200px; margin: 0 auto;">
        
        <section style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-top: 4px solid #00447c;">
            <h3 style="color: #00447c; margin-top: 0;">➕ Cadastrar Novo Militar</h3>
            <form method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
                <input type="hidden" name="action" value="create">
                
                <div>
                    <label style="font-size: 0.85em; color: #555; font-weight: bold;">Nome / Guerra</label>
                    <input type="text" name="name" placeholder="Ex: MN Silva" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                </div>
                
                <div>
                    <label style="font-size: 0.85em; color: #555; font-weight: bold;">Login (NIP)</label>
                    <input type="text" name="username" placeholder="Utilizador" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                </div>
                
                <div>
                    <label style="font-size: 0.85em; color: #555; font-weight: bold;">Senha Inicial</label>
                    <input type="password" name="password" placeholder="Senha provisória" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                </div>
                
                <div>
                    <label style="font-size: 0.85em; color: #555; font-weight: bold;">Perfil</label>
                    <select name="role" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                        <option value="Operador">Operador</option>
                        <option value="Enc_Financas">Encarregado de Finanças</option>
                        <option value="Ajudante_Encarregado">Ajudante do Encarregado</option>
                        <option value="Chefe_Departamento">Chefe de Departamento</option>
                        <option value="Vice_Diretor">Vice-Diretor</option>
                        <option value="Diretor">Diretor</option>
                        <option value="Admin">Administrador</option>
                        <option value="Usuário Comum">Usuário Comum (Consulta)</option>
                    </select>
                </div>
                
                <button type="submit" class="btn-primary" style="height: 42px; background: #28a745; border: none; color: white; border-radius: 4px; font-weight: bold; cursor: pointer;">CADASTRAR</button>
            </form>
        </section>

        <div style="background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; text-align: left;">
                        <th style="padding: 15px; color: #00447c;">Nome</th>
                        <th style="padding: 15px; color: #00447c;">Utilizador</th>
                        <th style="padding: 15px; color: #00447c;">Perfil</th>
                        <th style="padding: 15px; color: #00447c;">Trava de Senha</th>
                        <th style="padding: 15px; color: #00447c;">Ações Rápidas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px; font-weight: bold;"><?php echo htmlspecialchars($u['name']); ?></td>
                        <td style="padding: 12px;"><?php echo htmlspecialchars($u['username']); ?></td>
                        <td style="padding: 12px;"><?php echo htmlspecialchars($u['role']); ?></td>
                        <td style="padding: 12px;">
                            <?php echo $u['must_change_password'] 
                                ? '<span style="background: #ffc107; color: black; padding: 3px 8px; border-radius: 3px; font-size: 0.85em;">🔴 Exige Troca</span>' 
                                : '<span style="background: #28a745; color: white; padding: 3px 8px; border-radius: 3px; font-size: 0.85em;">🟢 Segura</span>'; ?>
                        </td>
                        <td style="padding: 12px;">
                            <form method="POST" style="display: flex; gap: 5px; align-items: center;">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                
                                <select name="role" style="padding: 6px; border-radius: 3px; border: 1px solid #ccc;">
                                    <option value="Operador" <?php echo $u['role'] == 'Operador' ? 'selected' : ''; ?>>Operador</option>
                                    <option value="Enc_Financas" <?php echo $u['role'] == 'Enc_Financas' ? 'selected' : ''; ?>>Enc. Finanças</option>
                                    <option value="Ajudante_Encarregado" <?php echo $u['role'] == 'Ajudante_Encarregado' ? 'selected' : ''; ?>>Ajudante Enc.</option>
                                    <option value="Chefe_Departamento" <?php echo $u['role'] == 'Chefe_Departamento' ? 'selected' : ''; ?>>Chefe Depto</option>
                                    <option value="Vice_Diretor" <?php echo $u['role'] == 'Vice_Diretor' ? 'selected' : ''; ?>>Vice-Diretor</option>
                                    <option value="Diretor" <?php echo $u['role'] == 'Diretor' ? 'selected' : ''; ?>>Diretor</option>
                                    <option value="Admin" <?php echo $u['role'] == 'Admin' ? 'selected' : ''; ?>>Admin</option>
                                    <option value="Usuário Comum" <?php echo $u['role'] == 'Usuário Comum' ? 'selected' : ''; ?>>Usr. Comum</option>
                                </select>
                                
                                <input type="password" name="password" placeholder="Nova Senha (Vazio = Manter)" style="width: 170px; padding: 6px; border-radius: 3px; border: 1px solid #ccc;">
                                
                                <button type="submit" style="background: #00447c; color: white; border: none; padding: 6px 12px; border-radius: 3px; cursor: pointer; font-weight: bold;">Salvar</button>
                                
                                <?php if($u['username'] !== 'admin'): ?>
                                    <a href="/admin/delete?id=<?php echo $u['id']; ?>" onclick="return confirm('Deseja realmente excluir este militar do sistema?')" style="background: #dc3545; color: white; text-decoration: none; padding: 6px 12px; border-radius: 3px; font-weight: bold;">Excluir</a>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <section style="background: #fff5f5; padding: 25px; border-radius: 8px; margin-top: 40px; border: 2px solid #dc3545; box-shadow: 0 4px 10px rgba(220,53,69,0.2);">
            <h3 style="color: #dc3545; margin-top: 0;">⚠️ ZONA DE PERIGO (Ferramentas de Limpeza)</h3>
            <p style="color: #666; font-size: 0.95em; margin-bottom: 20px;">Utilize estas ferramentas exclusivamente durante a fase de testes ou manutenção. As ações são irreversíveis e os ficheiros PDF físicos serão permanentemente eliminados do servidor.</p>
            
            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                
                <div style="flex: 1; background: white; padding: 20px; border-radius: 6px; border: 1px solid #ffc107;">
                    <h4 style="margin-top: 0; color: #856404; font-size: 1.1em;">🧹 Limpar Apenas Processos (Wipe Dados)</h4>
                    <p style="font-size: 0.85em; color: #555; margin-bottom: 20px;">Apaga todos os documentos, despachos, histórico de eventos, notas de empenho e PDFs do servidor. <strong>Os utilizadores cadastrados são mantidos intactos.</strong></p>
                    <form action="/admin/reset_docs" method="POST" onsubmit="return confirm('TEM A CERTEZA? Todos os processos, históricos e PDFs serão apagados para sempre!');">
                        <button type="submit" style="background: #ffc107; color: #000; font-weight: bold; border: none; padding: 12px 20px; border-radius: 4px; cursor: pointer; width: 100%;">Executar Limpeza de Processos</button>
                    </form>
                </div>

                <div style="flex: 1; background: white; padding: 20px; border-radius: 6px; border: 1px solid #dc3545;">
                    <h4 style="margin-top: 0; color: #721c24; font-size: 1.1em;">☢️ Reset Total do Sistema (Factory Reset)</h4>
                    <p style="font-size: 0.85em; color: #555; margin-bottom: 20px;">Apaga TUDO (Processos, PDFs e Utilizadores). O sistema é formatado e apenas o utilizador <strong>admin</strong> (senha: <code>admin123</code>) será recriado.</p>
                    <form action="/admin/factory_reset" method="POST" onsubmit="return confirm('ALERTA MÁXIMO MILITAR: Isto vai apagar todo o sistema, ficheiros e utilizadores, além de fechar a sua sessão. Confirma a formatação?');">
                        <button type="submit" style="background: #dc3545; color: white; font-weight: bold; border: none; padding: 12px 20px; border-radius: 4px; cursor: pointer; width: 100%;">Executar Formatação Total</button>
                    </form>
                </div>

            </div>
        </section>

    </main>
</body>
</html>