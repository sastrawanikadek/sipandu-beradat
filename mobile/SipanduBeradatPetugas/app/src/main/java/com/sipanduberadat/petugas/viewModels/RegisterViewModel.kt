package com.sipanduberadat.petugas.viewModels

import android.location.Location
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.sipanduberadat.petugas.models.Banjar
import com.sipanduberadat.petugas.models.DesaAdat
import com.sipanduberadat.petugas.models.Kabupaten
import com.sipanduberadat.petugas.models.Kecamatan
import java.util.*

class RegisterViewModel: ViewModel() {
    val name = MutableLiveData<String>("")
    val username = MutableLiveData<String>("")
    val password = MutableLiveData<String>("")
    val phone = MutableLiveData<String>("")
    val dateOfBirth = MutableLiveData<String>("")
    val nik = MutableLiveData<String>("")
    val gender = MutableLiveData<String>("")
    val category = MutableLiveData<String>("")
    val kabupaten = MutableLiveData<Long>(-1)
    val kecamatan = MutableLiveData<Long>(-1)
    val desaAdat = MutableLiveData<Long>(-1)
    val banjar = MutableLiveData<Long>(-1)
    val avatar = MutableLiveData<ByteArray>()
    val homeLocation = MutableLiveData<Location>()
    val dateOfBirthDate = MutableLiveData<Calendar>(Calendar.getInstance())
    val kabupatens = MutableLiveData<List<Kabupaten>>(listOf())
    val kecamatans = MutableLiveData<List<Kecamatan>>(listOf())
    val desaAdats = MutableLiveData<List<DesaAdat>>(listOf())
    val banjars = MutableLiveData<List<Banjar>>(listOf())
}