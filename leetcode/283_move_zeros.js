var nums = [1, 0, 1, 0, 3, 12, 0, 1];
var d = 1;

for(var i = 0; i < nums.length-d; i++){
    while (nums[i] == 0 && nums[i+d] == 0) {
        d++;
    }
    if (nums[i] == 0 & i+d < nums.length) {
        var tmp = nums[i];
        nums[i] = nums[i+d];
        nums[i+d] = tmp;
    }
}

console.log(nums);
