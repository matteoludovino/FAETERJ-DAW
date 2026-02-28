<?php
$resultado = '';
$numero1 = '';
$numero2 = '';
$operacao = 'soma';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $numero1 = $_POST["numero1"];
    $numero2 = $_POST["numero2"];
    $operacao = $_POST["operacao"];
    
    $num1 = floatval($numero1);
    $num2 = floatval($numero2);
    
    if ($numero1 !== '' && $numero2 !== '') {
        switch ($operacao) {
            case 'soma':
                $conta = $num1 + $num2;
                $resultado = "$num1 + $num2 = $conta";
                break;
                
            case 'subtracao':
                $conta = $num1 - $num2;
                $resultado = "$num1 - $num2 = $conta";
                break;
                
            case 'multiplicacao':
                $conta = $num1 * $num2;
                $resultado = "$num1 × $num2 = $conta";
                break;
                
            case 'divisao':
                if ($num2 != 0) {
                    $conta = $num1 / $num2;
                    $resultado = "$num1 ÷ $num2 = $conta";
                } else {
                    $resultado = "Erro: Divisão por zero não é permitida!";
                }
                break;
        }
    } else {
        $resultado = "Por favor, preencha os dois números!";
    }
    
    echo $resultado;
}
?>