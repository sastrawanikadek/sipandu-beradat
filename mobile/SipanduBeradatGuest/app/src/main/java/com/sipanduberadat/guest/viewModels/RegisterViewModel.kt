package com.sipanduberadat.guest.viewModels

import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.sipanduberadat.guest.models.*
import java.util.*

class RegisterViewModel: ViewModel() {
    val name = MutableLiveData("")
    val email = MutableLiveData("")
    val username = MutableLiveData("")
    val password = MutableLiveData("")
    val phone = MutableLiveData("")
    val dateOfBirth = MutableLiveData("")
    val identityType = MutableLiveData("")
    val identityNumber = MutableLiveData("")
    val gender = MutableLiveData("")
    val country = MutableLiveData<Long>(-1)
    val accommodation = MutableLiveData<Long>(-1)
    val avatar = MutableLiveData<ByteArray>()
    val dateOfBirthDate = MutableLiveData(Calendar.getInstance())
    val countries = MutableLiveData<List<Negara>>(listOf())
    val accommodations = MutableLiveData<List<Akomodasi>>(listOf())
}