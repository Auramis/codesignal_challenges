class SpaceXEmulator {

    private $registers;
    
    function __construct(){
        $this->registers = new SplFixedArray(43);
        for($i = 0; $i < count($this->registers); $i++){
            $this->registers[$i] = 0;
        }
    }
    
    function runSubroutine($subroutine){
        $currentCmd = 0;
        $subroutineLength = count($subroutine);   

        while($currentCmd < $subroutineLength){
            $this->execute($subroutine[$currentCmd], $currentCmd);
        }

        return (string)$this->registers[42];
    }
    
    private static function codeToReg($code){
        return (int)substr($code, 1);
    }
    
    private function execute($cmd, &$cmdIndex){

        $instruction = explode(' ', $cmd);
        $arg = self::codeToReg($instruction[1]);
        $regs = &$this->registers;
        $jumped = false;

        switch($instruction[0]){
            case 'MOV':
                $args = explode(',', $instruction[1]);
                if(is_numeric($args[0])) {
                    $regs[self::codeToReg($args[1])] = $args[0];
                } else $regs[self::codeToReg($args[1])] = $regs[self::codeToReg($args[0])];
                break; 
            case 'ADD':     
                $args = explode(',', $instruction[1]);

                $regs[self::codeToReg($args[0])] = ($regs[self::codeToReg($args[0])] + $regs[self::codeToReg($args[1])]) % (2 ** 32);
                if($regs[self::codeToReg($args[0])] < 0) $regs[self::codeToReg($args[0])] = $regs[self::codeToReg($args[0])] + (2 ** 32); 
                break;
            case 'DEC':
                $regs[$arg] = ($regs[$arg] !== 0) ? $regs[$arg] - 1 : (2 ** 32 - 1);
                break;
            case 'INC':
                $regs[$arg] = ($regs[$arg] !== (2 ** 32 - 1)) ? $regs[$arg] + 1 : 0;
                break;
            case 'INV':
                $regs[$arg] = ~ $regs[$arg];
                break;
            case 'JMP':
                $cmdIndex = $instruction[1] - 1;
                $jumped = true;
                break;
            case 'JZ':
                if($regs[0] == 0){
                    $cmdIndex = $instruction[1] - 1;
                    $jumped = true;
                }
                break;
        }
        if(!$jumped) $cmdIndex++;
    }
}

function cpuEmulator($subroutine) {
    $emu = new SpaceXEmulator();
    return $emu->runSubroutine($subroutine);
}
    
